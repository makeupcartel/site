<?php
namespace Unirgy\RapidFlowPro\Model\ResourceModel;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Db\Select;
use Unirgy\RapidFlowPro\Model\ResourceModel\Category\AbstractCategory;

class Category
    extends AbstractCategory
{
    protected $_csvRows;

    protected function _construct()
    {
        parent::_construct();

        $this->_logger = $this->_context->logger;
        $this->_scopeConfig = $this->_context->scopeConfig;
        $this->_modelProductImage = $this->_context->modelProductImage;
    }

    public function export()
    {
        $tune = $this->_scopeConfig->getValue('urapidflow/finetune');
        if (!empty($tune['export_page_size']) && $tune['export_page_size'] > 0) {
            $this->_pageRowCount = (int)$tune['export_page_size'];
        }
        if (!empty($tune['page_sleep_delay'])) {
            $this->_pageSleepDelay = (int)$tune['page_sleep_delay'];
        }

        $profile = $this->_profile;
        $logger = $profile->getLogger();

        $this->_entityTypeId = $this->_getEntityType($this->_entityType, 'entity_type_id');
        $this->_prepareEntityIdField();
        $entityId = $this->_entityIdField;

        $this->_profile->activity('Preparing data');

        $this->_prepareAttributes($profile->getAttributeCodes());
        $this->_prepareSystemAttributes();
        $this->_prepareCategories();

        $storeId = $profile->getStoreId();
        $this->_storeId = $storeId;

        $baseUrl = $this->_storeManager->getStore($storeId)->getBaseUrl(UrlInterface::URL_TYPE_WEB);
        $mediaUrl = $this->_storeManager->getStore($storeId)->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $mediaDir = $this->_filesystem->getDirectoryRead('media')->getAbsolutePath();
        $imgModel = $this->_modelProductImage;

        $this->_upPrependRoot = $profile->getData('options/export/urlpath_prepend_root');

        // main product table
        $table = $this->_t(self::TABLE_CATALOG_CATEGORY_ENTITY);

        $rootCatId = $this->_getRootCatId();
        $rootPath = $rootCatId ? '1/' . $rootCatId : '1';

        if ($this->_upPrependRoot) {
            $nameAttrId = $this->_attr('name', 'attribute_id');
            $rootCatPathsSel = $this->_read->select()
                ->from(['w' => $this->_t(self::TABLE_STORE_WEBSITE)], [])
                ->join(['g' => $this->_t(self::TABLE_STORE_GROUP)], 'g.group_id=w.default_group_id', [])
                ->join(['e' => $table], "e.{$entityId}=g.root_category_id", ["concat('1/',e.{$entityId})"])
                ->join(['name' => $table . '_varchar'],
                       "name.{$entityId}=e.{$entityId} and name.attribute_id={$nameAttrId} and name.value<>'' and name.value is not null and name.store_id=0",
                       ['value'])
                ->group("e.{$entityId}");
            if ($storeId) {
                $rootCatPathsSel->where("e.{$entityId}=?", $rootCatId);
            }
            $this->_rootCatPaths = $this->_read->fetchPairs($rootCatPathsSel);
        }

        // start select
        $upAttrId = $this->_attr('url_path', 'attribute_id');

        $select = $this->_read->select()->from(['e' => $table])->order('path');

            $select->join(['up' => $table . '_varchar'],
                          "up.{$entityId}=e.{$entityId} and up.attribute_id={$upAttrId} and up.value<>'' and up.value is not null and up.store_id=0",
                          []);

        if ($this->_upPrependRoot && !empty($this->_rootCatPaths)) {
            $_rcPaths = [];
            foreach ($this->_rootCatPaths as $_rcPath => $_rcName) {
                $_rcPaths[] = $this->_read->quoteInto('path=?', $_rcPath);
                $_rcPaths[] = $this->_read->quoteInto('path like ?', $_rcPath . '/%');
            }
            $select->where(implode(' OR ', $_rcPaths));
        } else {
            $select->where(
                $this->_read->quoteInto('path=?', $rootPath)
                . $this->_read->quoteInto(' OR path like ?', $rootPath . '/%')
            );
        }

        if ($storeId != 0) {
            $select->joinLeft(['ups' => $table . '_varchar'],
                              "ups.{$entityId}=e.{$entityId} AND ups.attribute_id={$upAttrId} 
                              AND ups.value<>'' AND up.value IS NOT NULL AND ups.store_id='{$storeId}'",
                              []);
            $select->columns(['url_path' => 'IFNULL(ups.value, up.value)']);
        } else {
            $select->columns(['url_path' => 'up.value']);
        }

        $this->_attrJoined = [$upAttrId];

        $columns = $profile->getColumns();

        $defaultSeparator = $profile->getData('options/csv/multivalue_separator');
        if (!$defaultSeparator) {
            $defaultSeparator = '; ';
        }

        $this->_fields = [];
        $this->_fieldsCodes = [];
        if ($columns) {
            foreach ($columns as $i => &$f) {
                if (empty($f['alias'])) {
                    $f['alias'] = $f['field'];
                }
                if (!empty($f['default']) && is_array($f['default'])) {
                    $f['default'] = implode(!empty($f['separator']) ? $f['separator'] : $defaultSeparator, $f['default']);
                }
                $this->_fields[$f['alias']] = $f;
                $this->_fieldsCodes[$f['field']] = true;
            }
            unset($f);
        } else {
            foreach ($this->_attributesByCode as $k => $a) {
                $this->_fields[$k] = ['field' => $k, 'title' => $k, 'alias' => $k];
                $this->_fieldsCodes[$k] = true;
            }
        }

        $condProdIds = $profile->getConditionsProductIds();
        if (is_array($condProdIds)) {
            $select->where("{$entityId} in (?)", $condProdIds);
        }

        $countSelect = clone $select;
        $countSelect->reset(Select::FROM)->reset(Select::COLUMNS)->from(['e' => $table], ['count(*)']);
        $count = $this->_read->fetchOne($countSelect);
        unset($countSelect);
        $profile->setRowsFound($count)->setStartedAt(\Unirgy\RapidFlow\Helper\Data::now())
            ->sync(true, ['rows_found', 'started_at'], false);
        $profile->activity('Exporting');
#memory_get_usage();
        // open export file
        $profile->ioOpenWrite();

        // write headers to the file
        $headers = [];
        foreach ($this->_fields as $k => $f) {
            $headers[] = !empty($f['alias']) ? $f['alias'] : $k;
        }
        $profile->ioWriteHeader($headers);

        // batch size
        // repeat until data available
        // data will loaded page by page to conserve memory
        for ($page = 0; ; $page++) {
            // set limit for current page
            $select->limitPage($page + 1, $this->_pageRowCount);
            // retrieve product entity data and attributes in filters
            $rows = $this->_read->fetchAll($select);
            if (!$rows) {
                break;
            }
            // fill $this->_entities associated by product id
            $this->_entities = [];
            foreach ($rows as $p) {
                $this->_entities[$p[$entityId]][0] = $p;
            }
            unset($rows);

            $this->_entityIds = array_keys($this->_entities);
            $this->_attrValueIds = [];
            $this->_attrValuesFetched = [];
            $this->_fetchAttributeValues($storeId, true);
            $this->_csvRows = [];
//            memory_get_usage(true);

            $this->_eventManager->dispatch('urapidflow_catalog_category_export_before_format', [
                'vars' => [
                    'profile' => $this->_profile,
                    'products' => &$this->_entities,
                    'fields' => &$this->_fields,
                ]
            ]);

            // format product data as needed
            foreach ($this->_entities as $id => $p) {
                $csvRow = [];
                $value = null;
                foreach ($this->_fields as $k => $f) {
                    $attr = $f['field'];
                    $inputType = $this->_attr($attr, 'frontend_input');

                    // retrieve correct value for current row and field
                    if ($v = $this->_attr($attr, 'force_value')) {
                        $value = $v;
                    } elseif (!empty($this->_fieldAttributes[$attr])) {
                        $a = $this->_fieldAttributes[$attr];
                        $value = isset($p[$storeId][$a]) ? $p[$storeId][$a] : (isset($p[0][$a]) ? $p[0][$a] : null);
                    } else {
                        $value = isset($p[$storeId][$attr]) ? $p[$storeId][$attr] : (isset($p[0][$attr]) ? $p[0][$attr] : null);
                    }

                    // replace raw numeric values with source option labels
                    if (($inputType === 'select' || $inputType === 'multiselect') && ($options = $this->_attr($attr,
                                                                                                            'options'))
                    ) {

                        if (!is_array($value)) {
                            $value = explode(',', $value);
                        }
                        foreach ($value as &$v) {
                            if ($v === '') {
                                continue;
                            }
                            if (!isset($options[$v])) {
                                $profile->addValue('num_warnings');
                                $logger->warning(__("Unknown option '%1' for category '%2' attribute '%3'", $v,
                                                    $p[0]['url_path'], $attr));
                                continue;
                            }
                            $v = $options[$v];
                        }
                        unset($v);
                    }

                    // combine multiselect values
                    if (is_array($value)) {
                        $value = implode(!empty($f['separator']) ? $f['separator'] : $defaultSeparator, $value);
                    }

                    // process special cases of loaded attributes
                    switch ($attr) {
                        // product url
                        case 'url_path':
                            if (empty($value)) {
                                $value = isset($this->_categories[$id]['url_path']) ? $this->_categories[$id]['url_path'] : $this->catBuildPath($p[0]);
                                //$value = $this->catBuildPath($p[0], $this->_categories);
                            }
                            if (!empty($f['format']) && $f['format'] === 'url') {
                                $value = $baseUrl . $value;
                            } else {
                                $value = $this->_upPrependRoot($p[0], $value);
                            }
                            break;

                        case 'const.value':
                            $value = isset($f['default']) ? $f['default'] : '';
                            break;
                    }

                    switch ($this->_attr($attr, 'backend_type')) {
                        case 'decimal':
                            if (null !== $value && !empty($f['format'])) {
                                $value = sprintf($f['format'], $value);
                            }
                            break;

                        case 'datetime':
                            if (!\Unirgy\RapidFlow\Helper\Data::is_empty_date($value)) {
                                $value = date(!empty($f['format']) ? $f['format'] : 'Y-m-d H:i:s', strtotime($value));
                            }
                            break;
                    }

                    switch ($this->_attr($attr, 'frontend_input')) {
                        case 'media_image':
                            if ($value === 'no_selection') {
                                $value = '';
                            }
                            if (!empty($value) && !empty($f['format']) && $f['format'] === 'url') {
                                try {
                                    $path = $imgModel->setBaseFile($value)->getBaseFile();
                                    $path = str_replace($mediaDir . DIRECTORY_SEPARATOR, '', $path);
                                    $value = $mediaUrl . str_replace(DIRECTORY_SEPARATOR, '/', $path);
                                } catch (\Exception $e) {
                                    $value = '';
                                }
                            }
                            break;
                    }

                    if ((null === $value || $value === '') && !empty($f['default'])) {
                        $value = $f['default'];
                    }

                    $csvRow[] = $value;
                }

                $this->_csvRows[] = $this->_convertEncoding($csvRow);
//                $profile->ioWrite($csvRow);
                $profile->addValue('rows_processed');//->addValue('rows_success');
            } // foreach ($this->_entities as $id=>&$p)

            $this->_eventManager->dispatch('urapidflow_catalog_category_export_before_output', [
                'vars' => [
                    'profile' => $this->_profile,
                    'products' => &$this->_entities,
                    'fields' => &$this->_fields,
                    'rows' => &$this->_csvRows,
                ]
            ]);

            foreach ($this->_csvRows as $row) {
                $profile->ioWrite($row);
                $profile->addValue('rows_success');
            }

            $profile->setMemoryUsage(memory_get_usage(true))->setMemoryPeakUsage(memory_get_peak_usage(true))
                ->setSnapshotAt(\Unirgy\RapidFlow\Helper\Data::now())->sync();

            $this->_checkLock();

            // stop repeating if this is the last page
            if (count($this->_entities) < $this->_pageRowCount) {
                break;
            }
            if ($this->_pageSleepDelay) {
                sleep($this->_pageSleepDelay);
            }
        } // while (true)
        $profile->ioClose();
    }

    public function import()
    {
        $tune = $this->_scopeConfig->getValue('urapidflow/finetune');
        if (!empty($tune['import_page_size']) && $tune['import_page_size'] > 0) {
            $this->_pageRowCount = (int)$tune['import_page_size'];
        }
        if (!empty($tune['page_sleep_delay'])) {
            $this->_pageSleepDelay = (int)$tune['page_sleep_delay'];
        }

        $profile = $this->_profile;
        $this->_prepareEntityIdField();

        $dryRun = $profile->getData('options/import/dryrun');

        if ($this->_storeManager->isSingleStoreMode()) {
            $storeId = 0;
        } else {
            $storeId = $profile->getStoreId();
        }
        $this->_storeId = $storeId;
        $this->_entityTypeId = $this->_getEntityType($this->_entityType, 'entity_type_id');
        $this->_attributeSetId = $this->_getEntityType($this->_entityType, 'default_attribute_set_id');

        $useTransactions = $profile->getUseTransactions();

        $this->_profile->activity(__('Fetching number of rows'));

        $profile->ioOpenRead();
        $count = -1;
        while ($profile->ioRead()) {
            $count++;
        }
        $profile->setRowsFound($count)->setStartedAt(\Unirgy\RapidFlow\Helper\Data::now())
            ->sync(true, array('rows_found', 'started_at'), false);
        $profile->activity(__('Preparing data'));
        $profile->ioSeekReset();

        $this->_importPrepareColumns();
        $this->_prepareAttributes(array_keys($this->_fieldsCodes));
        $this->_prepareSystemAttributes();
        $this->_importValidateColumns();
        $this->_prepareCategories();

        $eventVars = [
            'profile' => &$this->_profile,
            'old_data' => &$this->_entities,
            'new_data' => &$this->_newData,
            'url_paths' => &$this->_urlPaths,
            'attr_value_ids' => &$this->_attrValueIds,
            'valid' => &$this->_valid,
            'insert_entity' => &$this->_insertEntity,
            'change_attr' => &$this->_changeAttr,
        ];

        $this->_profile->activity(__('Importing'));

        $this->_isLastPage = false;

        // data will loaded page by page to conserve memory
        for ($page = 0; ; $page++) {
            $this->_startLine = 2 + $page * $this->_pageRowCount;
            try {
                $this->_checkLock();

                if ($useTransactions && !$dryRun) {
                    $this->_write->beginTransaction();
                }

                $this->_importResetPageData();
                $this->_importFetchNewData();
                $this->_importFetchOldData();
                $this->_fetchAttributeValues($storeId, true);
                $this->_importProcessNewData();

                $this->_checkLock();

                $this->_eventManager->dispatch('urapidflow_category_import_after_fetch', ['vars' => $eventVars]);
                $this->_importValidateNewData();
                $this->_eventManager->dispatch('urapidflow_category_import_after_validate', ['vars' => $eventVars]);
                $this->_importProcessDataDiff();
                $this->_eventManager->dispatch('urapidflow_category_import_after_diff', ['vars' => $eventVars]);

                if (!$dryRun) {
                    $this->_importSaveEntities();
                    $this->_importGenerateAttributeValues();
                    $this->_importSaveAttributeValues();
                    $this->_eventManager->dispatch('urapidflow_category_import_after_save', ['vars' => $eventVars]);
                }

                $profile->setMemoryUsage(memory_get_usage(true))->setMemoryPeakUsage(memory_get_peak_usage(true))
                    ->setSnapshotAt(\Unirgy\RapidFlow\Helper\Data::now())->sync();

                if ($useTransactions && !$dryRun) {
                    $this->_write->commit();
                }

                if(!$dryRun){
                    $this->_updateUrlRewrites();
                }
            } catch (\Exception $e) {
                if ($useTransactions && !$dryRun) {
                    $this->_write->rollBack();
                }
#print_r($e);
                throw $e;
            }
            if ($this->_isLastPage) {
                break;
            }
            if ($this->_pageSleepDelay) {
                sleep($this->_pageSleepDelay);
            }
        }

        $profile->ioClose();
    }

    public function fetchSystemAttributes()
    {
        $this->_entityTypeId = $this->_getEntityType($this->_entityType, 'entity_type_id');
        $this->_prepareSystemAttributes();
        return $this->_attributesByCode;
    }

}
