<?php

namespace Unirgy\RapidFlow\Model\ResourceModel\AbstractResource;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Db\Select;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Unirgy\RapidFlow\Helper\Data as HelperData;
use Unirgy\RapidFlow\Model\Config as ModelConfig;
use Unirgy\RapidFlow\Model\Profile;
use Unirgy\RapidFlow\Model\ResourceModel\AbstractResource;

class Fixed
    extends AbstractResource
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var ModelConfig
     */
    protected $_rapidFlowConfig;

    protected $_dataType;

    protected $_rowTypeSelect = [];

    protected $_rowTypeCount = [];

    protected $_totalCount = 0;

    protected $_rowNum = 0;

    protected $_cnt = [];

    protected $_newRows = [];

    protected $_newRowTypes = [];

    protected $_newRowActions = [];

    protected $_newRowMethods = [];

    protected $_newRefreshHoRoPids = [];

    protected $_isLastPage = false;

    protected $_rowTypes = [];

    protected $_rowTypeFields = [];

    protected $_exportConvertFields = [];

    protected $_startLine;

    /**
     *
     * @throws \RuntimeException
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_scopeConfig = $this->_context->scopeConfig;
        $this->_logger = $this->_context->logger;
        $this->_rapidFlowConfig = $this->_context->rapidFlowConfig;
    }

    protected function _initImportEventVars(&$eventVars)
    {

    }

    public function import()
    {
        $benchmark = false;
        /** @var Profile $profile */
        $profile = $this->_profile;
        $logger = $profile->getLogger();

        $this->_prepareEntityIdField();
        $tune = $this->_scopeConfig->getValue('urapidflow/finetune');
        if (!empty($tune['export_page_size']) && $tune['export_page_size'] > 0) {
            $this->_pageRowCount = (int)$tune['export_page_size'];
        }
        if (!empty($tune['page_sleep_delay'])) {
            $this->_pageSleepDelay = (int)$tune['page_sleep_delay'];
        }
        if (!empty($tune['curl_connect_timeout'])) {
            $this->_curlConnectTimeout = (int)$tune['curl_connect_timeout'];
        }
        if (!empty($tune['curl_timeout'])) {
            $this->_curlTimeout = (int)$tune['curl_timeout'];
        }
        if (!empty($tune['curl_useragent'])) {
            $this->_curlUserAgent = $tune['curl_useragent'];
        }
        if (!empty($tune['curl_customrequest'])) {
            $this->_curlCustomRequest = $tune['curl_customrequest'];
        }
        if (!empty($tune['curl_headers'])) {
            $this->_curlHeaders = array_filter(preg_split("/\r\n|\n\r|\r|\n/", $tune['curl_headers']));
        }

        $this->_cnt = [];
        $rowNum = 0;

        $this->_profile->activity('Fetching number of rows');

        $profile->ioOpenRead();
        $count = 0;
        while ($profile->ioRead()) {
            $count++;
        }
        $profile->setRowsFound($count)->setStartedAt(HelperData::now())->sync(true, ['rows_found', 'started_at'],
                                                                              false);
        $profile->ioSeekReset();

        $this->_rowTypes = (array)$profile->getData('options/row_types');

        $this->_prepareRowTypeData();

        $eventVars = [
            'profile' => &$this->_profile,
            'skus' => &$this->_skus,
            'new_rows' => &$this->_newRows,
            'new_row_types' => &$this->_newRowTypes,
            'new_row_actions' => &$this->_newRowActions,
            'new_row_methods' => &$this->_newRowMethods,
        ];
        $this->_initImportEventVars($eventVars);

        $this->_profile->activity('Importing');
#memory_get_usage(true);
        if ($benchmark) $this->_logger->debug("============================= IMPORT START: " . memory_get_usage(true) . ', ' . memory_get_peak_usage(true));

        $this->_isLastPage = false;

        $this->_getStoreIds();

        $count = 0;
        // data will loaded page by page to conserve memory
        for ($page = 0; ; $page++) {
            $this->_startLine = 1 + $page * $this->_pageRowCount;

            $this->_checkLock();

#memory_get_usage(true);
            if ($benchmark) $this->_logger->debug("================ PAGE START: " . memory_get_usage(true) . ', ' . memory_get_peak_usage(true));

            $this->_importResetPageData();
#memory_get_usage(true);
            if ($benchmark) $this->_logger->debug("_importResetPageData: " . memory_get_usage(true) . ', ' . memory_get_peak_usage(true));
            $this->_importFetchNewData();
#memory_get_usage(true);
            if ($benchmark) $this->_logger->debug("_importFetchNewData: " . memory_get_usage(true) . ', ' . memory_get_peak_usage(true));
            $this->_importProcessNewData();
#memory_get_usage(true);
            if ($benchmark) $this->_logger->debug("_importProcessNewData: " . memory_get_usage(true) . ', ' . memory_get_peak_usage(true));

            $this->_eventManager->dispatch('urapidflow_product_extra_import_after_fetch', ['vars' => $eventVars]);

            $this->_checkLock();

            $this->_importSaveRows();

            $this->_refreshHasOptionsRequiredOptions($this->_newRefreshHoRoPids);

            $this->_eventManager->dispatch('urapidflow_product_extra_import_after_save', array('vars' => $eventVars));

#memory_get_usage(true);
            if ($benchmark) $this->_logger->debug("_importSaveRows: " . memory_get_usage(true) . ', ' . memory_get_peak_usage(true));

            $profile->setMemoryUsage(memory_get_usage(true))->setMemoryPeakUsage(memory_get_peak_usage(true))
                ->setSnapshotAt(HelperData::now())->sync();

            if ($this->_isLastPage) {
                break;
            }
            if ($this->_pageSleepDelay) {
                sleep($this->_pageSleepDelay);
            }
        }

        $profile->setMemoryUsage(memory_get_usage(true))->setMemoryPeakUsage(memory_get_peak_usage(true))
            ->setSnapshotAt(HelperData::now())->sync();

        $this->_profile->activity(__('Running after import procedures'));

        $this->_afterImport($this->_cnt);

        $profile->ioClose();

        return $this;
    }

    protected function _prepareRowTypeData()
    {
        $rowTypes = $this->_rapidFlowConfig->getRowTypes($this->_dataType);
        $this->_rowTypeFields = [];
        foreach ($rowTypes as $rowType => $rowNode) {
            foreach ($rowNode->columns as $fieldName => $fieldNode) {
                $this->_rowTypeFields[$rowType][$fieldName] = $fieldNode;
            }
        }
    }

    protected function _importResetPageData()
    {
        $this->_newRows = [];
        $this->_newRowTypes = [];
        $this->_newRowActions = [];
        $this->_newRowMethods = [];
        $this->_newRefreshHoRoPids = [];
    }

    protected function _importFetchNewData()
    {
        $profile = $this->_profile;

        $defaultSeparator = $profile->getData('options/csv/multivalue_separator');
        if (!$defaultSeparator) {
            $defaultSeparator = ';';
        }

        for ($i1 = 0; $i1 < $this->_pageRowCount; $i1++) {
            $row = $profile->ioRead();
            if (!$row) {
                // last row
                $this->_isLastPage = true;
#var_dump($this->_newData);
                return true;
            }
            if ($row[0] === '' || empty($row[0])) {
                $profile->addValue('rows_processed')->addValue('rows_empty');
                continue;
            }

            $method = false;
            $rowType = trim($row[0]);
            $rowAction = $rowType[0];
            switch ($rowAction) {
                case '#': // comment
                    break;

                case '-': // delete
                    $rowType = substr($rowType, 1);
                    $method = '_deleteRow' . $rowType;
                    break;

                case '%': // rename
                    $rowType = substr($rowType, 1);
                    $method = '_renameRow' . $rowType;
                    break;

                case '+': // add/update
                    $rowType = substr($rowType, 1);
                    $method = '_importRow' . $rowType;
                    break;

                default: // add/update
                    $rowAction = '+';
                    $method = '_importRow' . $rowType;
            }
            if ($method === false) {
                $profile->addValue('rows_empty');
                continue;
            }
            if (!is_callable(array($this, $method))) {
                $profile->addValue('rows_processed')->addValue('rows_errors')->addValue('num_errors');
                $profile->getLogger()->setLine($i1+1)->error(__('Invalid row type: %1', $rowType));
//                throw new LocalizedException(__('Invalid row type: %1', $rowType));
            }
            if ($this->_rowTypes && !in_array($rowType, $this->_rowTypes)) {
                $profile->addValue('rows_processed')->addValue('rows_nochange');
                continue;
            }

            $lineNum = $this->_startLine + $i1;
            $this->_newRows[$lineNum] = $row;
            $this->_newRowTypes[$lineNum] = $rowType;
            $this->_newRowActions[$lineNum] = $rowAction;
            $this->_newRowMethods[$lineNum] = $method;
        }
        return false;
    }

    protected function _importProcessNewData()
    {
        //placeholder
    }

    protected function _importSaveRows()
    {
        $profile = $this->_profile;
        $logger = $profile->getLogger();

        foreach ($this->_newRows as $lineNum => $row) {
            try {
                $profile->addValue('rows_processed');
                $logger->setLine($lineNum)->setColumn(0);

                $result = null;
                #echo '<br/>'.$rowNum.', '.$row[0].', ';
                #echo $rowNum.' ';
                $this->_row = $row;

                $method = $this->_newRowMethods[$lineNum];
                $result = $this->$method($row);
                $this->_cnt[$row[0]] = empty($this->_cnt[$row[0]]) ? 1 : $this->_cnt[$row[0]] + 1;

                switch ($result) {
                    case self::IMPORT_ROW_RESULT_SUCCESS:
                        $profile->addValue('rows_success');
                        $logger->success();
                        break;
                    case self::IMPORT_ROW_RESULT_NOCHANGE:
                        $profile->addValue('rows_nochange');
                        break;
                    case self::IMPORT_ROW_RESULT_EMPTY:
                        $profile->addValue('rows_empty');
                        break;
                    case self::IMPORT_ROW_RESULT_DEPENDS:
                        $profile->addValue('rows_depends');
                        break;
                    case self::IMPORT_ROW_RESULT_ERROR:
                        $profile->addValue('rows_errors')->addValue('num_errors');
                        break;
                }
            } catch (\Exception $e) {
                $result = self::IMPORT_ROW_RESULT_ERROR;
                $profile->addValue('rows_errors')->addValue('num_errors');
                $logger->error($e->getMessage());
            }
            $this->_cnt[$result] = empty($this->_cnt[$result]) ? 1 : $this->_cnt[$result] + 1;
        }

        return $this;
    }

    protected function _afterImport($cnt)
    {

    }

    /**
     * Export one or multiple row types
     *
     * @throws \Unirgy\RapidFlow\Exception\Stop
     */
    public function export()
    {
        $profile = $this->_profile;
        $logger = $profile->getLogger();
        $this->_prepareEntityIdField();

        $tune = $this->_scopeConfig->getValue('urapidflow/finetune');
        if (!empty($tune['export_page_size']) && $tune['export_page_size'] > 0) {
            $this->_pageRowCount = (int)$tune['export_page_size'];
        }
        if (!empty($tune['page_sleep_delay'])) {
            $this->_pageSleepDelay = (int)$tune['page_sleep_delay'];
        }
        if (!empty($tune['curl_connect_timeout'])) {
            $this->_curlConnectTimeout = (int)$tune['curl_connect_timeout'];
        }
        if (!empty($tune['curl_timeout'])) {
            $this->_curlTimeout = (int)$tune['curl_timeout'];
        }
        if (!empty($tune['curl_useragent'])) {
            $this->_curlUserAgent = $tune['curl_useragent'];
        }
        if (!empty($tune['curl_customrequest'])) {
            $this->_curlCustomRequest = $tune['curl_customrequest'];
        }
        if (!empty($tune['curl_headers'])) {
            $this->_curlHeaders = array_filter(preg_split("/\r\n|\n\r|\r|\n/", $tune['curl_headers']));
        }

        $this->_profile->activity(__('Preparing data'));

        $profile->ioOpenWrite();
        $rowTypes = $profile->getData('options/row_types');
        if (!$rowTypes) {
            $rowTypes = array_keys($this->_rapidFlowConfig->getRowTypes($profile->getDataType()));
        }

        $counts = [];
        $totalCount = 0;
        foreach ($rowTypes as $rowType) {
            $method = '_exportInit' . $rowType;
            if (!is_callable([$this, $method])) {
                $this->_logger->warning('Not callable: ' . $rowType . ': ' . $method);
                continue;
            }
            // initialize export select query
            $this->$method();
            if (!$this->_select) {
                $this->_logger->warning('No select: ' . $method);
                continue;
            }
            if(!empty($tune['debug_sql'])){
                $this->_profile->getLogger()->notice('Select for : ' . $method);
                $this->_profile->getLogger()->notice((string)$this->_select);
            }
            $this->_rowTypeCount[$rowType] = $this->_fetchRowCount($this->_select);
            if ($this->_rowTypeCount[$rowType]) {
                $this->_rowTypeSelect[$rowType] = $this->_select;
                $totalCount += 1 + $this->_rowTypeCount[$rowType];
            }
        }
        $profile->setRowsFound($totalCount)
            ->setStartedAt(HelperData::now())
            ->sync(true, ['rows_found', 'started_at'], false);

        $this->_rowNum = 0;
        foreach ($this->_rowTypeSelect as $rowType => $select) {
            $this->_checkLock();

            $profile->activity(__('Exporting: %1', $rowType));

            $this->_select = $select;
            $this->_exportRowType($rowType);

            if ($this->_pageSleepDelay) {
                sleep($this->_pageSleepDelay);
            }
        }
        $profile->ioClose();
    }

    /**
     * @param Select $select
     * @return string
     */
    protected function _fetchRowCount($select)
    {
        $countSelect = clone $select;

        $countSelect->reset(Select::ORDER);
        $countSelect->reset(Select::LIMIT_COUNT);
        $countSelect->reset(Select::LIMIT_OFFSET);

        if (method_exists($countSelect, 'columns')) {
            $countSelect->reset(Select::COLUMNS)->columns('COUNT(*)', 'main');
            $count = $this->_read->fetchOne($countSelect);
        } else {
            $sql = (string) $countSelect;
            $sql = preg_replace('/^select\s+.+?\s+from\s+/is', 'SELECT count(*) FROM ', $sql);
            $count = $this->_read->fetchOne($sql);
        }

        return $count;
    }

    protected $_csvRows = [];

    protected function _exportRowType($rowType)
    {
        $profile = $this->_profile;
        $logger = $profile->getLogger();

        $this->_exportConvertFields = [];

        $cbMethod = !empty($this->_exportRowCallback[$rowType]) ? $this->_exportRowCallback[$rowType] : null;

        // fetch rows data
        if ($this->_profile->getData('options/debug')) {
            $logger->log('DEBUG', (string)$this->_select);
        }
        $result = $this->_select->query();
        $row = $result->fetch();
        $columns = [];
        if ($row) {
            $columns = $this->getRowTypeColumns($rowType);
            $header = array_keys($columns);
            array_unshift($header, '##' . $rowType);
            $profile->ioWriteHeader($header);
            $logger->setLine(++$this->_rowNum);
            $profile->addValue('rows_processed')->addValue('rows_success');
        }
        $count = 0;
        $this->_csvRows = [];
        while ($row) {
            $logger->setLine(++$this->_rowNum);
            $count++;
            if ($cbMethod) {
                try {
                    if ($this->$cbMethod($row) === false) {
                        --$this->_rowNum;
                        $row = $result->fetch();
                        continue;
                    }
                } catch (\Exception $e) {
                    $profile->addValue('rows_errors');
                    $logger->error($e->getMessage());
                }
            }
            foreach ($this->_exportConvertFields as $k) {
                $row[$k] = $this->_convertEncoding($row[$k]);
            }
            $r = array($rowType);
            foreach ($columns as $k => $c) {
                if (!isset($row[$k])) {
                    $r[] = '';
                    continue;
                }
                $v = $row[$k];
                $r[] = isset($this->_attrOptionsByValue[$k][$v]) ? $this->_attrOptionsByValue[$k][$v] : $v;
            }

            $r = $this->_convertEncoding($r);
            //$profile->ioWrite($r);
            $this->_csvRows[] = $r;
            $profile->addValue('rows_processed');

            if ($count == $this->_pageRowCount) {
                $profile->setMemoryUsage(memory_get_usage(true))->setMemoryPeakUsage(memory_get_peak_usage(true))
                    ->setSnapshotAt(HelperData::now())->sync();

                $this->_checkLock();

                if ($this->_pageSleepDelay) {
                    sleep($this->_pageSleepDelay);
                }
                $count = 0;
            }

            $row = $result->fetch();
        }
        $this->_eventManager->dispatch('urapidflow_fixed_export_before_output', [
            'vars' => [
                'profile' => $this->_profile,
                'row_type' => $rowType,
                'columns' => $columns,
                'rows' => &$this->_csvRows,
            ]
        ]);

        foreach ($this->_csvRows as $row) {
            $profile->ioWrite($row);
            $profile->addValue('rows_success');
        }

        $profile->setMemoryUsage(memory_get_usage(true))->setMemoryPeakUsage(memory_get_peak_usage(true))
            ->setSnapshotAt(HelperData::now())->sync();
    }

    protected function _skipStore($storeId, $column = 0, $noEmpty = true)
    {
        if ($noEmpty && !$storeId) {
            $this->_profile->getLogger()->setColumn($column);
            throw new LocalizedException(__('Invalid store'));
        }
        return $this->_storeIds && !in_array($storeId, $this->_storeIds);
    }

    /**
     * @param $rowType
     * @return array
     */
    protected function getRowTypeColumns($rowType)
    {
        return $this->_rapidFlowConfig->getRowTypeColumns($rowType);
    }

    protected function _initEmptySelect()
    {
        $productTable = $this->_t(self::TABLE_CATALOG_PRODUCT_ENTITY);
        $this->_select = $this->_read->select()
            ->from(['main' => $productTable], ['sku'])
            ->where('false');
    }
}
