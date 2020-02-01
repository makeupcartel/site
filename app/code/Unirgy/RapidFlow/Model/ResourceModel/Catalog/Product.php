<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    \Unirgy\RapidFlow
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

/**
 * Special attributes:
 *
 * EXPORT ONLY:
 * category.path
 * category.name
 *
 * const.value
 *
 * product.store
 *
 * IMPORT/EXPORT:
 * stock.use_config_manage_stock
 * stock.manage_stock
 * stock.is_in_stock
 * stock.qty
 *
 * product.attribute_set
 * product.type
 * product.websites
 */

namespace Unirgy\RapidFlow\Model\ResourceModel\Catalog;

use Magento\Catalog\Model\Product\Image;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Db\Select;
use Magento\Framework\Stdlib\DateTime;
use Magento\Staging\Model\VersionManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;
use Unirgy\RapidFlow\Exception\Row;
use Unirgy\RapidFlow\Helper\Data as HelperData;
use Unirgy\RapidFlow\Model\Profile\Catalog\Product as ProductProfile;
use Unirgy\RapidFlow\Model\ResourceModel\Catalog\Product\AbstractProduct;

/**
 * Class Product
 * @package Unirgy\RapidFlow\Model\ResourceModel\Catalog
 */
class Product
    extends AbstractProduct
{
    protected $_csvRows = [];

    /**
     * @var array of product skus that will need urls to be updated
     */
    protected $_urlUpdates = [];

    protected $_mediaUpdates = [];

    /**
     * @var \Unirgy\RapidFlow\Helper\Url
     */
    protected $_urlHelper;

    /**
     * @var \Unirgy\RapidFlow\Helper\ImageCache
     */
    protected $_imageCacheHelper;

    protected function setupProductFlatIdx()
    {
        /* todo need to test any non admin store if product_flat enabled */
        if ($this->_storeManager->getDefaultStoreView() && $this->_productFlatIndexState->isFlatEnabled()) {
            $this->_rtIdxFlatAttrCodes = $this->_productFlatIndexHelper->getAttributeCodes();
        }
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_urlHelper = $this->_context->urlHelper;
        $this->_imageCacheHelper = $this->_context->imageCacheHelper;
    }

    /**
     * actual export of formatted product data to file
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Unirgy\RapidFlow\Exception\Row
     * @throws \Unirgy\RapidFlow\Exception\Stop
     */
    public function export()
    {
        $benchmark = false;

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
        /** @var ProductProfile $profile */
        $profile = $this->_profile;
        $logger = $profile->getLogger();
        /** @var LoggerInterface $systemLogger */
        $systemLogger = $this->_logger;

        $this->_prepareEntityTypeId();

        $this->_profile->activity('Preparing data');

        $this->_prepareAttributes($profile->getAttributeCodes());
        $this->_prepareSystemAttributes();
        $this->setupProductFlatIdx();

        $storeId = $profile->getStoreId();
        $this->_storeId = $storeId;
//        $manageStock = $this->_scopeConfig->getValue('cataloginventory/item_options/manage_stock', $storeId);

        $secure = $profile->getData('options/export/image_https');
        /** @var Store $store */
        $store = $this->_storeManager->getStore($storeId);
        $baseUrl = $store->getBaseUrl('web', $secure);
        $mediaUrl = $store->getBaseUrl('media', $secure);
        $mediaDir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        /* @var $imgModel Image */
        $imgModel = $this->_modelProductImage;

        $exportImageFiles = $profile->getData('options/export/image_files');
        $imagesFromDir = $mediaDir . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'product';
        $imagesToDir = $profile->getImagesBaseDir();

        $this->_profile->activity(__('Loading products'));
        // main product table
        $table = $this->_t('catalog_product_entity');
        // start select
        $select = $this->_read->select()->from(['e' => $table]);
        $this->_attrJoined = [];
        $columns = $profile->getColumns();

        $exportInvalidValues = $profile->getData('options/export/invalid_values');
        $exportInternalValues = $profile->getData('options/export/internal_values');
        $skipOutOfStock = $profile->getData('options/export/skip_out_of_stock');
        $manageStock = (int)$this->_scopeConfig->getValue('cataloginventory/item_options/manage_stock',
                                                          ScopeInterface::SCOPE_STORE, $storeId);

        $defaultSeparator = $profile->getData('options/csv/multivalue_separator');
        if (!$defaultSeparator) {
            $defaultSeparator = '; ';
        }

        $entId = $this->_entityIdField;

        $this->_fields = [];
        $this->_fieldsCodes = [];
        if ($columns) {
            foreach ($columns as $i => &$f) {
                if (empty($f['alias'])) {
                    $f['alias'] = $f['field'];
                }
                if (!empty($f['default']) && is_array($f['default'])) {
                    $f['default'] = join(!empty($f['separator']) ? $f['separator'] : $defaultSeparator, $f['default']);
                }
                $f['column_num'] = $i + 1;
                $this->_fields[$f['alias']] = $f;
                $this->_fieldsCodes[$f['field']] = true;
                if ($f['field'] === 'product.configurable_parent_sku') {
                    $this->_configurableParentSku = $f;
                }
            }
            unset($f);
        } else {
            $columns = [];
            $i = 1;
            foreach ($this->_attributesByCode as $k => $a) {
                if ($k === 'product.entity_id') {
                    continue;
                }
                $columns[$i - 1] = array('field' => $k, 'title' => $k, 'alias' => $k, 'default' => '');
                $this->_fields[$k] = array('field' => $k, 'title' => $k, 'alias' => $k, 'column_num' => $i++);
                $this->_fieldsCodes[$k] = true;
            }
            $this->_configurableParentSku = [];
        }

        if ($this->_hasColumnsLike('category.')) {
            $this->_prepareCategories();
#memory_get_usage(true);

        }

        if ($skipOutOfStock) {
            $select->where("entity_id IN (SELECT product_id FROM {$this->_t('cataloginventory_stock_item')} WHERE (qty>0 && is_in_stock=1) OR NOT IF(use_config_manage_stock,{$manageStock},manage_stock))");
        }
        $condProdIds = $profile->getConditionsProductIds();
        if (is_array($condProdIds)) {
            $select->where("entity_id in (?)", $condProdIds);
        }

        if ($this->currentVersion && $this->currentVersion->getId()) {
            $select->setPart('disable_staging_preview', true);
            $select->where('e.created_in <= ?', $this->currentVersion->getId());
            $select->where('e.updated_in > ?', $this->currentVersion->getId());
        }

        $select->order('e.'.$entId);

        $countSelect = clone $select;
        $countSelect->reset(Select::FROM)->reset(Select::COLUMNS)->from(array('e' => $table), ['count(*)']);
        $count = $this->_read->fetchOne($countSelect);
        unset($countSelect);

        $profile->setRowsFound($count)
                ->setStartedAt(HelperData::now())
                ->sync(true, ['rows_found', 'started_at'], false);
        $profile->activity(__('Exporting'));
#memory_get_usage(true);
        if ($benchmark) {
            $systemLogger->debug($this->_systemDebugMessage(memory_get_usage(), memory_get_peak_usage(),
                'IMPORT START'));
        }
        // open export file
        $profile->ioOpenWrite();

        // write headers to the file
        $headers = [];
        foreach ($columns as $c) {
            $_hAlias = !empty($c['alias']) ? $c['alias'] : $c['field'];
            $headers[$_hAlias] = $_hAlias;
        }
        $profile->ioWriteHeader($headers);

        $rowNum = 1;

        // batch size
        // repeat until data available
        // data will loaded page by page to conserve memory
        for ($page = 0; ; $page++) {
#memory_get_usage(true);
            if ($benchmark) {
                $systemLogger->debug($this->_systemDebugMessage(memory_get_usage(), memory_get_peak_usage(),
                    'PAGE START'));
            }
            // set limit for current page
            $select->limitPage($page + 1, $this->_pageRowCount);
            // retrieve product entity data and attributes in filters
            $rows = $this->_read->fetchAll($select);
            if (!$rows) {
                break;
            }

            $this->_importResetPageData();

            unset($this->_products);

            // fill $this->_products associated by product id
            $this->_products = [];
            foreach ($rows as $p) {
                $this->_products[$p[$entId]][0] = $p;
                $this->_productIdToSeq[$p[$entId]] = $p['entity_id'];
            }
            unset($rows);
#memory_get_usage(true);
            if ($benchmark) {
                $systemLogger->debug($this->_systemDebugMessage(memory_get_usage(), memory_get_peak_usage(),
                    '_readRows'));
            }

            $this->_productIds = array_keys($this->_products);
            $this->_productSeqIds = array_values($this->_productIdToSeq);

            $this->_fetchAttributeValues($storeId, true);
#memory_get_usage(true);
            if ($benchmark) {
                $systemLogger->debug($this->_systemDebugMessage(memory_get_usage(), memory_get_peak_usage(),
                    '_fetchAttributeValues'));
            }
            $this->_fetchWebsiteValues();
#memory_get_usage(true);
            if ($benchmark) {
                $systemLogger->debug($this->_systemDebugMessage(memory_get_usage(), memory_get_peak_usage(),
                    '_fetchWebsiteValues'));
            }
            $this->_fetchStockValues();
#memory_get_usage(true);
            if ($benchmark) {
                $systemLogger->debug($this->_systemDebugMessage(memory_get_usage(), memory_get_peak_usage(),
                    '_fetchStockValues'));
            }
            $this->_fetchCategoryValues();
#memory_get_usage(true);
            if ($benchmark) {
                $systemLogger->debug($this->_systemDebugMessage(memory_get_usage(), memory_get_peak_usage(),
                    '_fetchCategoryValues'));
            }

            $this->_csvRows = [];

            $this->_exportProcessPrice();
            $this->_exportConfigurableParentSku();

            $this->_eventManager->dispatch('urapidflow_catalog_product_export_before_format', [
                'vars' => [
                    'profile' => $this->_profile,
                    'products' => &$this->_products,
                    'fields' => &$this->_fields,
                ]
            ]);

            // format product data as needed
            foreach ($this->_products as $id => $p) {
                $logger->setLine(++$rowNum)->setColumn(0);
                $csvRow = [];
                $value = null;
                foreach ($columns as $c) {
                    $attr = $c['field'];
                    $f = $this->_fields[$c['alias']];
                    $inputType = $this->_attr($attr, 'frontend_input');
                    $sourceModel = $this->_attr($attr, 'source_model');

                    // retrieve correct value for current row and field
                    if ($v = $this->_attr($attr, 'force_value')) {
                        $value = $v;
                    } elseif (!empty($this->_fieldAttributes[$attr])) {
                        $a = $this->_fieldAttributes[$attr];
                        $value = isset($p[$storeId][$a]) ? $p[$storeId][$a] : (isset($p[0][$a]) ? $p[0][$a] : null);
                    } else {
                        $value = array_key_exists($storeId, $p) && array_key_exists($attr, $p[$storeId])
                            ? $p[$storeId][$attr]
                            : (array_key_exists($attr, $p[0]) ? $p[0][$attr] : null);
                    }

                    if (($value === null || $value === '') && !empty($c['default'])) {
                        $value = $c['default'];
                    }

                    // replace raw numeric values with source option labels
                    if ((!$exportInternalValues || strpos($attr, 'category.') === 0)
                        && ($inputType === 'select' || $inputType === 'multiselect' || $sourceModel)
                        && ($options = $this->_attr($attr, 'options'))
                    ) {
                        if (!is_array($value) && $inputType === 'multiselect') {
                            $value = explode(',', $value);
                        } elseif (!is_array($value)) {
                            $value = array($value);
                        }
                        foreach ($value as $k => &$v) {
                            if ($v === '') {
                                continue;
                            }
                            if (!isset($options[$v])) {
                                $profile->addValue('num_warnings');
                                $logger->setColumn($f['column_num'])
                                    ->warning(__("Unknown option '%1' for product '%2' attribute '%3'", $v,
                                                 $p[0]['sku'], $attr));
                                if (!$exportInvalidValues) {
                                    unset($value[$k]);
                                }
                                continue;
                            }
                            $v = $options[$v];
                        }
                        unset($v);
                    }

                    // combine multiselect values
                    if (is_array($value)) {
                        $value = join(!empty($f['separator']) ? $f['separator'] : $defaultSeparator, $value);
                    }

                    // process special cases of loaded attributes
                    switch ($attr) {
                        // product url
                        case 'url_path':
                        case 'url_key':
                            if (!empty($f['format']) && $f['format'] === 'url') {
                                $value = $baseUrl . $value;
                                $urlSuffix = $this->_scopeConfig->getValue(
                                    'catalog/seo/product_url_suffix',
                                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                                    $profile->getStoreId()
                                );
                                if ($urlSuffix) {
                                    $value .= $urlSuffix;
                                }
                            }
                            break;

                        case 'const.value':
                            $value = isset($c['default']) ? $c['default'] : '';
                            break;

                        case 'const.function':
                            $value = '';
                            if (!empty($c['default'])) {
                                try {
                                    list($class, $func) = explode('::', $c['default']);
                                    $model = HelperData::om()->get($class);
                                    $value = call_user_func([$model, $func], $p, $c, $storeId);
                                } catch (\Exception $e) {
                                    $logger->setColumn($f['column_num'])
                                        ->warning(__("Exception for product '%1' attribute '%2': %3", $p[0]['sku'],
                                                     $attr, $e->getMessage()));
                                }
                            }
                            break;
                    }

                    switch ($this->_attr($attr, 'backend_type')) {
                        case 'decimal':
                            if ($value !== null && !empty($f['format'])) {
                                $value = sprintf($f['format'], $value);
                            }
                            break;

                        case 'datetime':
                            if (!HelperData::is_empty_date($value)) {
                                $value = date(!empty($f['format']) ? $f['format'] : 'Y-m-d H:i:s', strtotime($value));
                            }
                            break;
                    }

                    switch ($this->_attr($attr, 'frontend_input')) {
                        case 'media_image':
                            if ($value === 'no_selection') {
                                $value = '';
                            }
                            if ($value !== '' && $exportImageFiles) {
                                $logger->setColumn($f['column_num']);
                                $this->_copyImageFile($imagesFromDir, $imagesToDir, $value);
                            }
                            if (!empty($f['format']) && $f['format'] === 'url' && !empty($value)) {
                                try {
                                    $path = $imgModel->setBaseFile($value)->getBaseFile();
                                    $path = str_replace($mediaDir . DS, '', $path);
                                    $value = $mediaUrl . str_replace(DS, '/', $path);

                                } catch (\Exception $e) {
                                    $systemLogger->warning($e->getMessage());
                                    $systemLogger->warning($e->getTraceAsString());
                                    $value = '';
                                }
                            }
                            break;
                    }

                    if (empty($csvRow[$c['alias']])) {
                        $csvRow[$c['alias']] = $value;
                    }
                }

                $csvRow = $this->_convertEncoding($csvRow);
                #$profile->ioWrite($csvRow);
                $this->_csvRows[] = $csvRow;

                $profile->addValue('rows_processed');
            } // foreach ($this->_products as $id=>&$p)

            $this->_eventManager->dispatch('urapidflow_catalog_product_export_before_output', [
                'vars' => [
                    'profile' => $this->_profile,
                    'products' => &$this->_products,
                    'fields' => &$this->_fields,
                    'rows' => &$this->_csvRows,
                ]
            ]);

            foreach ($this->_csvRows as $row) {
                $profile->ioWrite($row);
                $profile->addValue('rows_success');
            }

            $profile->setMemoryUsage(memory_get_usage(true))->setMemoryPeakUsage(memory_get_peak_usage(true))
                ->setSnapshotAt(HelperData::now())->sync();

            $this->_checkLock();

            // stop repeating if this is the last page
            if (sizeof($this->_products) < $this->_pageRowCount) {
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
        $benchmark = false;// todo set benchmark from interface or url param?

        $tune = $this->_scopeConfig->getValue('urapidflow/finetune');
        if (!empty($tune['import_page_size']) && $tune['import_page_size'] > 0) {
            $this->_pageRowCount = (int)$tune['import_page_size'];
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

        $profile = $this->_profile;

        $this->_saveAttributesMethod = ''; #$profile->getData('options/import/save_attributes_method');
        $this->_insertAttrChunkSize = (int)$profile->getData('options/import/insert_attr_chunk_size');
        if (!$this->_insertAttrChunkSize) {
            $this->_insertAttrChunkSize = 100;
        }

        $dryRun = $profile->getData('options/import/dryrun');

        if ($this->_storeManager->isSingleStoreMode()) {
            $storeId = 0;
        } else {
            $storeId = $profile->getStoreId();
        }
        $this->_storeId = $storeId;
        $this->_prepareEntityTypeId();

        $useTransactions = $profile->getUseTransactions();

        $this->_profile->activity(__('Retrieving number of rows'));

        $profile->ioOpenRead();
        $count = -1;
        while ($profile->ioRead()) {
            $count++;
        }
        $profile->setRowsFound($count)->setStartedAt(HelperData::now())->sync(true, ['rows_found', 'started_at'],
                                                                              false);
        $profile->ioSeekReset();

        $this->_profile->activity('Preparing data');

        $this->_importPrepareColumns();
        $this->_prepareAttributes(array_keys($this->_fieldsCodes));
        $this->_prepareSystemAttributes();
        $this->setupProductFlatIdx();
        $this->_importValidateColumns();
        $this->_prepareWebsites();
        $this->_prepareCategories();

        #$profile->ioSeekReset(6700);

        $eventVars = [
            'profile' => &$this->_profile,
            'old_data' => &$this->_products,
            'new_data' => &$this->_newData,
            'skus' => &$this->_skus,
            'attr_value_ids' => &$this->_attrValueIds,
            'valid' => &$this->_valid,
            'insert_entity' => &$this->_insertEntity,
            'update_entity' => &$this->_updateEntity,
            'change_attr' => &$this->_changeAttr,
            'change_website' => &$this->_changeWebsite,
            'change_stock' => &$this->_changeStock,
            'change_category_product' => &$this->_changeCategoryProduct,
        ];

        $this->_profile->activity('Importing');
#memory_get_usage(true);
        if ($benchmark) $this->_logger->debug('============================= IMPORT START: ' . memory_get_usage(true) . ', ' . memory_get_peak_usage(true));

        $this->_isLastPage = false;

        // data will loaded page by page to conserve memory
        for ($page = 0; ; $page++) {
            $this->_startLine = 2 + $page * $this->_pageRowCount;
            try {
                $this->_checkLock();

                if ($useTransactions && !$dryRun) {
                    $this->_write->beginTransaction();
                }
#memory_get_usage(true);
                if ($benchmark) $this->_logger->debug('================ PAGE START: ' . memory_get_usage(true) . ', ' . memory_get_peak_usage(true));
                $this->_importResetPageData();
#memory_get_usage(true);
                if ($benchmark) $this->_logger->debug('_importResetPageData: ' . memory_get_usage(true) . ', ' . memory_get_peak_usage(true));
                $this->_importFetchNewData();
#memory_get_usage(true);
                if ($benchmark) $this->_logger->debug('_importFetchNewData: ' . memory_get_usage(true) . ', ' . memory_get_peak_usage(true));
                $this->_importFetchOldData();
#memory_get_usage(true);
                if ($benchmark) $this->_logger->debug('_importFetchOldData: ' . memory_get_usage(true) . ', ' . memory_get_peak_usage(true));
                $this->_fetchAttributeValues($storeId, true);
#memory_get_usage(true);
                if ($benchmark) $this->_logger->debug('_fetchAttributeValues: ' . memory_get_usage(true) . ', ' . memory_get_peak_usage(true));
                $this->_fetchWebsiteValues();
#memory_get_usage(true);
                if ($benchmark) $this->_logger->debug('_fetchWebsiteValues: ' . memory_get_usage(true) . ', ' . memory_get_peak_usage(true));
                $this->_fetchStockValues();
#memory_get_usage(true);
                if ($benchmark) $this->_logger->debug('_fetchStockValues: ' . memory_get_usage(true) . ', ' . memory_get_peak_usage(true));
                $this->_fetchCategoryValues();
#memory_get_usage(true);
                if ($benchmark) $this->_logger->debug('_fetchCategoryValues: ' . memory_get_usage(true) . ', ' . memory_get_peak_usage(true));

                $this->_importProcessNewData();
#memory_get_usage(true);
                if ($benchmark) $this->_logger->debug('_importProcessNewData: ' . memory_get_usage(true) . ', ' . memory_get_peak_usage(true));

                $this->_checkLock();

                $this->_eventManager->dispatch('urapidflow_product_import_after_fetch', ['vars' => $eventVars]);

                $this->_importValidateNewData();
#memory_get_usage(true);
                if ($benchmark) $this->_logger->debug('_importValidateNewData: ' . memory_get_usage(true) . ', ' . memory_get_peak_usage(true));
                $this->_eventManager->dispatch('urapidflow_product_import_after_validate', ['vars' => $eventVars]);

                $this->_importProcessDataDiff();
#memory_get_usage(true);
                if ($benchmark) $this->_logger->debug('_importProcessDataDiff: ' . memory_get_usage(true) . ', ' . memory_get_peak_usage(true));
                $this->_eventManager->dispatch('urapidflow_product_import_after_diff', ['vars' => $eventVars]);

                if (!$dryRun) {
                    $this->_importProcessRemoteImageBatch();
                    if ($benchmark) $this->_logger->debug('_importProcessRemoteImageBatch: ' . memory_get_usage(true) . ', ' . memory_get_peak_usage(true));
                    $this->_eventManager->dispatch('urapidflow_product_import_after_remote_image_batch', ['vars' => $eventVars]);

                    $this->_importSaveEntities();
#memory_get_usage(true);
                    if ($benchmark) $this->_logger->debug('_importSaveEntities: ' . memory_get_usage(true) . ', ' . memory_get_peak_usage(true));
//                    $this->_importCopyImageFiles();
#memory_get_usage(true);
                    if ($benchmark) $this->_logger->debug('_importCopyImageFiles: ' . memory_get_usage(true) . ', ' . memory_get_peak_usage(true));
                    $this->_importGenerateAttributeValues();
#memory_get_usage(true);
                    if ($benchmark) $this->_logger->debug('_importGenerateAttributeValues: ' . memory_get_usage(true) . ', ' . memory_get_peak_usage(true));

                    $this->_importSaveAttributeValues();
#memory_get_usage(true);
                    if ($benchmark) $this->_logger->debug('_importSaveAttributeValues: ' . memory_get_usage(true) . ', ' . memory_get_peak_usage(true));
                    $this->_importSaveWebsiteValues();
#memory_get_usage(true);
                    if ($benchmark) $this->_logger->debug('_importSaveWebsiteValues: ' . memory_get_usage(true) . ', ' . memory_get_peak_usage(true));
                    $this->_importSaveProductCategories();
#memory_get_usage(true);
                    if ($benchmark) $this->_logger->debug('_importSaveProductCategories: ' . memory_get_usage(true) . ', ' . memory_get_peak_usage(true));
                    $this->_importSaveStockValues();
#memory_get_usage(true);
                    if ($benchmark) $this->_logger->debug('_importSaveStockValues: ' . memory_get_usage(true) . ', ' . memory_get_peak_usage(true));

                    #$this->_importReindexProducts();
                    #$this->_importRefreshRewrites();
                    $this->_importUpdateImageGallery();
#memory_get_usage(true);
                    if ($benchmark) $this->_logger->debug('_importUpdateImageGallery: ' . memory_get_usage(true) . ', ' . memory_get_peak_usage(true));

                    $this->_eventManager->dispatch('urapidflow_product_import_after_save', array('vars' => $eventVars));

                    #$this->_profile->realtimeReindex(array_keys($this->_productIdsUpdated));
                    $this->_importRealtimeReindex();
                    $this->_enqueueUrlUpdates();
                    $this->_enqueueImageCacheFlush();

                    $this->_eventManager->dispatch('urapidflow_product_import_after_rtidx',
                                                   array('vars' => $eventVars));
                }

                $profile->setMemoryUsage(memory_get_usage(true))->setMemoryPeakUsage(memory_get_peak_usage(true))
                    #$profile->setMemoryUsage(memory_get_usage(true))->setMemoryPeakUsage(memory_get_peak_usage(true))
                    ->setSnapshotAt(HelperData::now())->sync();

                if ($useTransactions && !$dryRun) {
                    $this->_write->commit();
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

        $this->_afterImport();
    }

    public function fetchSystemAttributes()
    {
        $this->_prepareEntityTypeId();
        $this->_prepareSystemAttributes();
        $this->setupProductFlatIdx();
        return $this->_attributesByCode;
    }

    protected function _cleanupValues($attr, &$oldValue, &$newValue)
    {
        // trying to work around PHP's weakly typed mess...
        if (!empty($attr['frontend_input'])) {
            switch ($attr['frontend_input']) {
                case 'media_image':
                    if ($oldValue !== null) {
                        if ($oldValue == 'no_selection') {
                            $oldValue = '';
                        }
                    }
                    break;

                case 'multiselect':
                    if ($oldValue === null) {
                        $oldValue = [];
                    }
                    if ($newValue === '') {
                        $newValue = [];
                    }
                    break;
            }
        }
        if (!empty($attr['backend_type'])) {
            switch ($attr['backend_type']) {
                case 'int':
                    if ($newValue !== null && !is_array($newValue)) {
                        if ($newValue === '') {
                            $newValue = null;
                        } else {
                            $newValue = $this->_locale->getNumber($newValue);
                            if ($newValue != (int)$newValue) {
                                $this->_profile->addValue('num_errors');
                                $this->_profile->getLogger()->error(__('Invalid int value'));
                            } else {
                                $newValue = (int)$newValue;
                            }
                        }
                    }
                    if ($oldValue !== null && !is_array($oldValue)) {
                        if ($oldValue === '') {
                            $oldValue = null;
                        } else {
                            $oldValue = (int)$oldValue;
                        }
                    }
                    break;

                case 'decimal':
                    if ($newValue !== null) {
                        if ($newValue === '') {
                            $newValue = null;
                        } else {
                            $newValue = $this->_locale->getNumber($newValue);
                            if (!is_numeric($newValue)) {
                                $this->_profile->addValue('num_errors');
                                $this->_profile->getLogger()->error(__('Invalid decimal value'));
                            } else {
                                $newValue *= 1.0;
                            }
                        }
                    }
                    if ($oldValue !== null) {
                        if ($oldValue === '') {
                            $oldValue = null;
                        } else {
                            $oldValue *= 1.0;
                        }
                    }
                    break;

                case 'datetime':
                    if ($newValue !== null) {
                        if ($newValue === '') {
                            $newValue = null;
                        } else {
                            static $_dp;
                            if (null === $_dp) {
                                $_dp = $this->_scopeConfig->getValue('urapidflow/import_options/date_processor');
                                if ($_dp === 'date_parse_from_format' && !version_compare(phpversion(), '5.3.0',
                                                                                          '>=')
                                ) {
                                    $_dp = 'strtotime';
                                }
                            }
                            static $_attrFormat = [];
                            $_attrCode = $attr['attribute_code'];
                            if (!isset($_attrFormat[$_attrCode])) {
                                if (isset($this->_fields[$_attrCode]['format'])) {
                                    $_attrFormat[$_attrCode] = $this->_fields[$_attrCode]['format'];
                                } else {
                                    $_attrFormat[$_attrCode] = $this->_profile->getDefaultDatetimeFormat();
                                }
                                if ($_dp === 'zend_date') {
                                    $_attrFormat[$_attrCode] = \Zend_Locale_Format::convertPhpToIsoFormat($_attrFormat[$_attrCode]);
                                }
                            }
                            switch ($_dp) {
                                case 'zend_date':
                                    /** @var \Zend_Date $_zendDate */
                                    static $_zendDate;
                                    if (null === $_zendDate) {
                                        $_zendDate = new \Zend_Date($newValue, $_attrFormat[$_attrCode],
                                                                    $this->_profile->getProfileLocale());
                                    } else {
                                        $_zendDate->set($newValue, $_attrFormat[$_attrCode]);
                                    }
                                    $newValue = $_zendDate->toString(DateTime::DATETIME_INTERNAL_FORMAT);
                                    break;
                                case 'date_parse_from_format':
                                    $_phpDatetime = \DateTime::createFromFormat($_attrFormat[$_attrCode], $newValue);
                                    $newValue = $_phpDatetime->format('Y-m-d H:i:s');
                                    break;
                                default:
                                    $newValue = date('Y-m-d H:i:s', strtotime($newValue));
                                    break;
                            }
                            if (!$newValue) {
                                $this->_profile->addValue('num_errors');
                                $this->_profile->getLogger()->error(__('Invalid datetime value'));
                            }
                        }
                    }
                    if ($oldValue !== null) {
                        if ($oldValue === '') {
                            $oldValue = null;
                        }
                    }
                    break;

                case 'varchar':
                case 'text':
                    if ($oldValue === '' && $newValue === null) {
                        $newValue = '';
                    } elseif ($oldValue === null && $newValue === '') {
                        $newValue = null;
                    } elseif (is_numeric($newValue)) {
                        $newValue = (string)$newValue;
                    }
                    break;
            }
        }
    }

    /**
     * put your comment there...
     *
     * @return boolean last page
     */
    protected function _importFetchNewData()
    {
        $profile = $this->_profile;
        $logger = $profile->getLogger();

        $emptyValueStrategy = $profile->getData('options/import/empty_value_strategy');
        $defaultSeparator = $profile->getData('options/csv/multivalue_separator');
        if (!$defaultSeparator) {
            $defaultSeparator = ';';
        }

        // read rows from file into memory and collect skus
        $this->_newData = [];
        // $i1 should be preserved during the loop
        for ($i1 = 0; $i1 < $this->_pageRowCount; $i1++) {
            $error = false;
            $row = $profile->ioRead();
            if (!$row) {
                // last row
                $this->_isLastPage = true;
#var_dump($this->_newData);
                return true;
            }

            $empty = true;
            foreach ($row as $v) {
                if (trim($v) !== '') {
                    $empty = false;
                    break;
                }
            }
            if ($empty) {
                $profile->addValue('rows_empty');
                continue;
            }

            $profile->addValue('rows_processed');
            $logger->setLine($this->_startLine + $i1);
            if (empty($row[$this->_skuIdx])) {
                $profile->addValue('rows_errors')->addValue('num_errors');
                $logger->setColumn($this->_skuIdx + 1)->error(__('Empty SKU'));
                continue;
            }
            if (!empty($this->_newData[$row[$this->_skuIdx]])) {
                $profile->addValue('rows_errors')->addValue('num_errors');
                $logger->setColumn($this->_skuIdx + 1)->error(__('Duplicate SKU'));
                continue;
            }
            $sku = $row[$this->_skuIdx];
            $this->_skuLine[$sku] = $this->_startLine + $i1;
            $this->_newData[$sku] = $this->_newDataTemplate;
            $this->_defaultUsed[$sku] = $this->_newDataTemplate;

            $error = false;
            foreach ($row as $col => $v) {
                if (!isset($this->_fieldsIdx[$col])) {
                //if (!isset($this->_fieldsIdx[$col]) && $v !== '') {
                    $profile->addValue('num_warnings');
                    $logger->setColumn($col + 1)
                        ->warning(__('Column is out of boundaries, ignored'));
                    continue;
                }
                $_kk = (array)$this->_fieldsIdx[$col];
                $_v = $v;
                foreach ($_kk as $k) {
                    $v = $_v;
                    if ($k === false || in_array($k, array('const.value', 'const.function'))) {
                        continue;
                    }
                    $input = $this->_attr($k, 'frontend_input');
                    $multiselect = $input === 'multiselect';
                    $separator = trim(!empty($this->_fields[$k]['separator']) ? $this->_fields[$k]['separator'] : $defaultSeparator);
                    try {
                        $v = $this->_convertEncoding($v);
                    } catch (\Exception $e) {
                        $profile->addValue('num_warnings');
                        $logger->setColumn($col + 1)->warning($e);
                        #$error = true;
                    }
                    if ($v !== '') {
                        // options and multiselect
                        if ($input === 'select') {
                            $v = trim($v);
                        } elseif ($multiselect) {
                            $values = explode($separator, $v);
                            $v = [];
                            foreach ($values as $v1) {
                                $v1 = trim($v1);
                                if ($v1 !== '') {
                                    $v[] = $v1;
                                }
                            }
                            // check if field is category.path and if it is, make sure values are unique
                            if (in_array($k, ['category.path', 'category.name'])) {
                                $v = array_unique($v);
                            }
                        }
                    }
                    if ($v === '#EMPTY#' || $emptyValueStrategy === 'empty' && $v === '') {
                        $this->_newData[$sku][$k] = '';
                        unset($this->_defaultUsed[$sku][$k]);
                    } elseif ($v === ['#EMPTY#'] || $emptyValueStrategy === 'empty' && $v === ['']) {
                        $this->_newData[$sku][$k] = [''];
                        unset($this->_defaultUsed[$sku][$k]);
                    } elseif ($v === '#DEFAULT#' || $emptyValueStrategy === 'default' && $v === '') {
                        $this->_newData[$sku][$k] = !empty($this->_newDataTemplate[$k]) ? $this->_newDataTemplate[$k] : '';
                        unset($this->_defaultUsed[$sku][$k]);
                    } elseif ($v === ['#DEFAULT#'] || $emptyValueStrategy === 'default' && $v === ['']) {
                        $this->_newData[$sku][$k] = !empty($this->_newDataTemplate[$k]) ? [$this->_newDataTemplate[$k]] : [''];
                        unset($this->_defaultUsed[$sku][$k]);
                    } elseif (!isset($this->_defaultUsed[$sku][$k]) || $v !== '' && $v !== array()) {
                        $this->_newData[$sku][$k] = $v;
                        unset($this->_defaultUsed[$sku][$k]);
                    }
                }
            }
            if ($error) {
                unset($this->_newData[$sku]);
            }
        }
        return false;
    }

    protected function _importValidateNewData()
    {
        $profile = $this->_profile;
        $logger = $profile->getLogger();
        $autoCreateAttributeSets = $profile->getData('options/import/create_attributesets');
        $autoCreateOptions = $profile->getData('options/import/create_options');
        $autoCreateCategories = $profile->getData('options/import/create_categories');
        $changeTypeSet = $profile->getData('options/import/change_typeset');
        $actions = $profile->getData('options/import/actions');
        $allowSelectIds = $profile->getData('options/import/select_ids');
        $allowNotApplicable = $profile->getData('options/import/not_applicable');

        // find changed data
        foreach ($this->_newData as $sku => $p) {
            try {
                $logger->setLine($this->_skuLine[$sku]);
                // check if the product is new
                $isNew = empty($this->_skus[$sku]);
                $attrSetId = null;
                $oldProduct = $isNew ? [] : $this->_products[$this->_skus[$sku]][0];

                if (($isNew && $actions === 'update') || (!$isNew && $actions === 'create')) {
                    $profile->addValue('rows_nochange');
                    $this->_valid[$sku] = false;
                    continue;
                }

                // validate required attributes
                $this->_valid[$sku] = true;

                $k = 'product.type';
                $logger->setColumn(isset($this->_fieldsCodes[$k]) ? $this->_fieldsCodes[$k] + 1 : 0);
                if (isset($p[$k])) {
                    if (isset($this->_defaultUsed[$sku][$k])) {
                        $typeId = $p[$k];
                    } else {
                        $typeId = $this->_attr($k, 'options_bytext', $p[$k]);
                        if (!$isNew) {
                            if (!$changeTypeSet && $typeId != $oldProduct[$k]) {
                                $this->_newData[$sku][$k] = $oldProduct[$k];
                                $profile->addValue('num_warnings');
                                $logger->warning(__('Will not change product type for an existing product'));
                            }
                        } elseif (!$typeId) {
                            $profile->addValue('num_errors');
                            $logger->error(__('Empty or invalid product type for a new product'));
                            $this->_valid[$sku] = false;
                        }
                    }
                } else { // not set
                    if ($isNew) {
                        $profile->addValue('num_errors');
                        $logger->error(__('Empty or invalid product type for a new product'));
                        $this->_valid[$sku] = false;
                    } else {
                        $typeId = $this->_products[$this->_skus[$sku]][0]['product.type'];
                    }
                }

                $k = 'product.attribute_set';
                $logger->setColumn(isset($this->_fieldsCodes[$k]) ? $this->_fieldsCodes[$k] + 1 : 0);
                if (isset($p[$k])) {
                    if (isset($this->_defaultUsed[$sku][$k])) {
                        $attrSetId = $p[$k];
                    } else {
                        if ($allowSelectIds && ($v = $this->_attr($k, 'options', $p[$k]))) {
                            $attrSetId = $p[$k];
                        } else {
                            $attrSetId = $this->_attr($k, 'options_bytext', $p[$k]);
                        }
                        if (!$isNew) {
                            if (!$changeTypeSet && $attrSetId != $oldProduct[$k]) {
                                $attrSetId = $oldProduct[$k];
                                $profile->addValue('num_warnings');
                                $logger->warning(__('Will not change attribute set for an existing product'));
                            }
                        } elseif (!$attrSetId) {
                            if ($autoCreateAttributeSets && $p[$k]) {
                                $attrSetId = $this->_importCreateAttributeSet($p[$k]);
                                $profile->addValue('num_warnings');
                                $logger->warning(__("Created a new attribute set '%1'", $p[$k]));
                            } else {
                                $profile->addValue('num_errors');
                                $logger->error(__('Empty or invalid attribute set for a new product'));
                                $this->_valid[$sku] = false;
                            }
                        }
                    }
                } else {
                    if ($isNew) {
                        $profile->addValue('num_errors');
                        $logger->error(__('Empty or invalid attribute set for a new product'));
                        $this->_valid[$sku] = false;
                    } else {
                        $attrSetId = $this->_products[$this->_skus[$sku]][0]['product.attribute_set'];
                    }
                }

                // continue on error
                if (!$this->_valid[$sku]) {
                    $profile->addValue('rows_errors');
                    continue;
                }

                $p[$k] = $attrSetId;
                $this->_newData[$sku][$k] = $attrSetId;

                $attrSetFields = $this->_getAttributeSetFields($attrSetId);
                $typeId = !empty($typeId) ? $typeId : $oldProduct['product.type'];
                $isParentProduct = $typeId === 'configurable' || $typeId === 'grouped' || $typeId === 'bundle';

                $dynamic = (string)__('Dynamic');
//                $dynPrice = ($typeId === 'configurable' || $typeId === 'bundle')
                $dynPrice = $typeId === 'bundle'
                    && (isset($p['price_type']) && !empty($p['price_type'])
                        && in_array($p['price_type'],
                                    [$dynamic, \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC])
                        || !isset($p['price_type']) && isset($oldProduct['price_type']) && (int)$oldProduct['price_type'] === \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC);

                $dynWeight = ($typeId === 'configurable' || $typeId === 'bundle')
                    && (isset($p['weight_type']) && !empty($p['weight_type'])
                        && in_array($p['weight_type'], [$dynamic, 1])
                        || !isset($p['weight_type']) && !empty($oldProduct['weight_type']));

                if ($isNew) {
                    // check missing required columns
                    foreach ($this->_attributesByCode as $k => $attr) {
                        if (isset($p[$k]) || empty($attr['is_required'])) {
                            continue;
                        }
                        $appliesTo = empty($attr['apply_to']) || !empty($attr['apply_to'][$typeId]);
                        $inAttrSet = empty($attr['attribute_id']) || !empty($attrSetFields[$k]);
                        $dynAttr = $k === 'price' && $dynPrice || $k === 'weight' && $dynWeight;
                        $parentQty = $k === 'stock.qty' && $isParentProduct;

                        if ($appliesTo && $inAttrSet && !$dynAttr && !$parentQty) {
                            $profile->addValue('num_errors');
                            $logger->setColumn(1);
                            $logger->error(__("Missing required value for '%1'", $k));
                            $this->_valid[$sku] = false;
                        }
                    }
                }

                // walk the attributes
                foreach ($p as $k => $newValue) {
                    $attr = $this->_attr($k);
                    $logger->setColumn(isset($this->_fieldsCodes[$k]) ? $this->_fieldsCodes[$k] + 1 : -1);

                    $empty = $newValue === null || $newValue === '' || $newValue === [];
                    $required = !empty($attr['is_required']);
                    $visible = !empty($attr['is_visible']);
                    $appliesTo = empty($attr['apply_to']) || !empty($attr['apply_to'][$typeId]);
                    $inAttrSet = empty($attr['attribute_id']) || !empty($attrSetFields[$k]);
                    $selectable = !empty($attr['frontend_input']) && ($attr['frontend_input'] === 'select' || $attr['frontend_input'] === 'multiselect' || !empty($attr['source_model']));
                    $dynAttr = $k === 'price' && $dynPrice || $k === 'weight' && $dynWeight;
                    $parentQty = $k === 'stock.qty' && $isParentProduct;

                    if (!$empty && $visible && (!$appliesTo || !$inAttrSet || $dynAttr) && !$allowNotApplicable) {
                        #var_dump($k, $newValue); echo "<hr>";
                        #$this->_newData[$sku][$k] = null;
                        unset($this->_newData[$sku][$k]);
                        $newValue = null;
                        $profile->addValue('num_warnings');
                        if (!$appliesTo) {
                            $logger->warning(__("The attribute '%1' does not apply to product type '%2', and will not be imported",
                                                $k, $typeId));
                        } elseif (!$inAttrSet) {
                            $attrSetName = $this->_attr('product.attribute_set', 'options', $attrSetId);
                            $logger->warning(__("The attribute '%1' does not apply to attribute set '%2', and will not be imported",
                                                $k, $attrSetName));
                        } elseif ($dynAttr) {
                            $logger->warning(__("The attribute '%1' is not used, as it is a dynamic value in this product, and will not be imported",
                                                $k));
                        }
                    } elseif ($empty && $required && $appliesTo && $inAttrSet && !$dynAttr && !$parentQty) {
                        if ($typeId === 'configurable' && $selectable
                            && !empty($attr['is_global']) && !empty($attr['is_configurable'])
                        ) {
//                            $select = "SELECT product_id FROM {$this->_t('catalog_product_super_attribute')} WHERE product_id={$this->_skus[$sku]} AND attribute_id={$attr['attribute_id']}";
                            $select = $this->_write->select()->from($this->_t(self::TABLE_CATALOG_PRODUCT_SUPER_ATTRIBUTE),
                                                                    'product_id')
                                ->where('product_id=?', $this->_skus[$sku])
                                ->where('attribute_id=?', $attr['attribute_id']);
                            if (!$isNew && !$this->_write->fetchOne($select)) {
                                $profile->addValue('num_warnings');
                                $logger->warning(__("If the attribute '%2' will not used in configurable subproducts, this value might be missing",
                                                    $k));
                            }
                        } else {
                            $profile->addValue('num_errors');
                            $logger->error(__("Missing required value for '%1'", $k));
                            $this->_valid[$sku] = false;
                            continue;
                        }
                    }

                    if ($selectable && !$empty && $k !== 'product.attribute_set') {
                        if ($attr['frontend_input'] === 'multiselect' && is_array($newValue)) {
                            $newValue = array_unique($newValue);
                        }
                        foreach ((array)$newValue as $i => $v) {
                            $vLower = strtolower(trim($v));
                            if ($k === 'category.name') {
                                $delimiter = !empty($this->_fields[$k]['delimiter']) ? $this->_fields[$k]['delimiter'] : '>';
                                $vLower = str_replace($delimiter, '>', $vLower);
                            }
                            if (isset($this->_defaultUsed[$sku][$k])
                                && !in_array($k, ['category.name', 'category.path'])
                            ) {
                                // default value used, no mapping required
                            } else if (isset($attr['options_bytext'][$vLower])) {
                                $vId = $attr['options_bytext'][$vLower];
                                if (is_array($newValue)) {
                                    #if (!is_array($this->_newData[$sku][$k])) {
                                    #    if ($vId!=$this->_newData[$sku][$k]) {
                                    #        $this->_newData[$sku][$k] = array($this->_newData[$sku][$k], $vId);
                                    #    }
                                    #} else {
                                    $this->_newData[$sku][$k][$i] = $vId;
                                    #}
                                } else {
                                    $this->_newData[$sku][$k] = $vId;
                                }
                            } else if ($allowSelectIds && isset($attr['options'][$v])) {
                                // select ids used, no mapping required
                            } else {
                                if ($k === 'category.name') {
                                    if ($autoCreateCategories) {
                                        $newOptionId = $this->_importCreateCategory($v);
                                        if (is_array($newValue)) {
                                            $this->_newData[$sku][$k][$i] = $newOptionId;
                                        } else {
                                            $this->_newData[$sku][$k] = $newOptionId;
                                        }
                                        $profile->addValue('num_warnings');
                                        $logger->warning(__("Created a new category '%1'", $v));
                                    } else {
                                        $profile->addValue('num_errors');
                                        $logger->error('Invalid category: ' . $v);
                                        $this->_valid[$sku] = false;
                                    }
                                } elseif ($autoCreateOptions && !empty($attr['attribute_id']) && (empty($attr['source_model']) || $attr['source_model'] === 'Magento\Eav\Model\Entity\Attribute\Source\Table')) {
                                    $newOptionId = $this->_importCreateAttributeOption($attr, $v);
                                    if (is_array($newValue)) {
                                        $this->_newData[$sku][$k][$i] = $newOptionId;
                                    } else {
                                        $this->_newData[$sku][$k] = $newOptionId;
                                    }
                                    $profile->addValue('num_warnings');
                                    $logger->warning(__("Created a new option '%1' for attribute '%2'", $v, $k));
                                } else {
                                    if ($k !== 'product.websites'
                                        || !$this->_rapidFlowHelper->hasEeGwsFilter()
                                    ) {
                                        $profile->addValue('num_errors');
                                        $logger->error(__("Invalid option '%1'", $v));
                                        $this->_valid[$sku] = false;
                                    }
                                }
                            }
                        } // foreach ((array)$newValue as $v)
                        if ($k === 'product.websites'
                            && $this->_rapidFlowHelper->hasEeGwsFilter()
                        ) {
                            $wIdsOrig = (array)$this->_newData[$sku][$k];
                            $this->_newData[$sku][$k] = $this->_rapidFlowHelper->filterEeGwsWebsiteIds($wIdsOrig);
                            if ($wIdsSkipped = array_diff($wIdsOrig, $this->_newData[$sku][$k])) {
                                $logger->warning(__('You are not allowed to associate products with this websites: %1',
                                                    implode(',', $wIdsSkipped)));
                            }
                        }
                    }
                } // foreach ($p as $k=>$newValue)

                if (!$this->_valid[$sku]) {
                    $profile->addValue('rows_errors');
                }
            } catch (Row $e) {
                $logger->error($e->getMessage());
                $profile->addValue('rows_error');
            }
        } // foreach ($this->_newData as $p)
        unset($p);
    }

    protected function _importProcessDataDiff()
    {
        $profile = $this->_profile;
        $logger = $profile->getLogger();
        $storeId = $this->_storeId;
        $dryRun = (bool)$profile->getData('options/import/dryrun');
        $stockZeroOut = $profile->getData('options/import/stock_zero_out');

        $forceUrlRewritesRefresh = $profile->getData('options/import/force_urlrewrite_refresh');
        $deleteOldCat = $profile->getData('options/import/delete_old_category_products');
        $sameAsDefault = $profile->getData('options/import/store_value_same_as_default');
        $importImageFiles = $profile->getData('options/import/image_files');
        $imagesFromDir = $profile->getImagesBaseDir();
        $imagesToDir = $this->_filesystem->getDirectoryWrite('media')->getAbsolutePath() . 'catalog' . DIRECTORY_SEPARATOR . 'product';

//        $hasCategoryIds = $this->_rapidFlowHelper->hasMageFeature('product.category_ids');
//        $hasRequiredOptions = $this->_rapidFlowHelper->hasMageFeature('product.required_options');

        $oldValues = [];

        $defMinQty = $this->_scopeConfig->getValue(
            'cataloginventory/item_options/min_qty',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $profile->getStoreId()
        );

        // find changed data
        foreach ($this->_newData as $sku => $p) {
            try {
                if (!$this->_valid[$sku]) {
                    continue;
                }
                $logger->setLine($this->_skuLine[$sku]);

                // check if the product is new
                $isNew = empty($this->_skus[$sku]);

                // create new product
                if ($isNew) {
                    $hasOptions = $p['product.type'] === 'configurable' || $p['product.type'] === 'bundle';

                    $this->_insertEntity[$sku] = [
                        'attribute_set_id' => $p['product.attribute_set'],
                        'type_id' => $p['product.type'],
                        'sku' => $sku,
                        'created_at' => HelperData::now(),
                        'updated_at' => HelperData::now(),
                        'has_options' => $hasOptions || !empty($p['product.has_options']) ? 1 : 0,
                    ];

                    if ($this->_rapidFlowHelper->hasMageFeature(self::ROW_ID)) {
                        // if row id is present, there is sequence table constraint
                        $this->_insertEntity[$sku]['entity_id'] = $this->_getNextProductSequence($dryRun);
                        $this->_insertEntity[$sku]['created_in'] = 1;
                        $this->_insertEntity[$sku]['updated_in'] = VersionManager::MAX_VERSION;
                    }

                    $this->_insertEntity[$sku]['required_options'] = $hasOptions || !empty($p['product.required_options']) ? 1 : 0;
                    $pId = null;

                    $this->addProductForUrlUpdate($sku);
                } else {
                    $pId = $this->_skus[$sku];
                }
                $isUpdated = false;
                
                if ($stockZeroOut && isset($p['stock.qty'])) {
                    if (!empty($p['stock.use_config_min_qty'])) {
                        $minQty = $defMinQty;
                    } else {
                        $minQty = isset($p['stock.min_qty']) ? $p['stock.min_qty'] : $defMinQty;
                    }
                    if (!isset($p['stock.is_in_stock'])) {
                        $p['stock.is_in_stock'] = $p['stock.qty'] > $minQty;
                    }
                    if (!isset($this->_fieldsCodes['stock.is_in_stock'])) {
                        $this->_fieldsCodes['stock.is_in_stock'] = $this->_fieldsCodes['stock.qty'];
                    }
                }
                // walk the attributes
                foreach ($p as $k => $newValue) {
                    $__isUpdated = false;
                    $logger->setColumn(isset($this->_fieldsCodes[$k]) ? $this->_fieldsCodes[$k] + 1 : 0);

                    $oldValue = !$pId ? null : (
                        isset($this->_products[$pId][$storeId][$k]) ? $this->_products[$pId][$storeId][$k] : (
                            isset($this->_products[$pId][0][$k]) ? $this->_products[$pId][0][$k] : null
                        )
                    );
                    $attr = $this->_attr($k);

                    // some validation happens here as well
                    $this->_cleanupValues($attr, $oldValue, $newValue);

                    if (strpos($k, 'stock.') === 0) {
                        if ($oldValue !== $newValue && $newValue !== null) {
                            list(, $f) = explode('.', $k, 2);
                            $this->_changeStock[$sku][$f] = $newValue;
                            if (!$isNew && isset($this->_fieldsCodes[$k])) {
                                $logger->success($newValue);
                            }
                            $isUpdated = true;
                        }
                        continue;
                    }
                    if (!$isNew) {
                        if ($k === 'product.attribute_set' && $this->_products[$pId][0]['product.attribute_set'] != $newValue) {
                            $this->_updateEntity[$pId]['attribute_set_id'] = $newValue;
                            $isUpdated = true;
                        }
                        if ($k === 'product.type' && $this->_products[$pId][0]['product.type'] != $newValue) {
                            $this->_updateEntity[$pId]['type_id'] = $newValue;
                            $isUpdated = true;
                        }
                        if ($k === 'product.has_options' && $this->_products[$pId][0]['product.has_options'] != $newValue) {
                            $this->_updateEntity[$pId]['has_options'] = $newValue;
                            $isUpdated = true;
                        }
                        if ($k === 'product.required_options' && $this->_products[$pId][0]['product.required_options'] != $newValue) {
                            $this->_updateEntity[$pId]['required_options'] = $newValue;
                            $isUpdated = true;
                        }
                    }
                    if ($k === 'product.websites') {
                        $oldValue = (array)$oldValue;
                        $newValue = (array)$newValue;
                        $oldValue = $this->_rapidFlowHelper->filterEeGwsWebsiteIds($oldValue);
                        $newValue = $this->_rapidFlowHelper->filterEeGwsWebsiteIds($newValue);
                        $insert = array_diff($newValue, $oldValue);
                        $delete = array_diff($oldValue, $newValue);
                        if ($insert || $delete) {
                            $this->_changeWebsite[$sku] = ['I' => $insert, 'D' => $delete];
                            if (!$isNew) {
                                $logger->success(sprintf('new websites: %s, old websites: %s',
                                                         implode(', ', $newValue),
                                                         implode(', ', $oldValue)));
                            }
                            $isUpdated = true;
                            $this->addProductForUrlUpdate($sku);
                        }
                        continue;
                    }
                    if (($k === 'category.ids' || $k === 'category.path' || $k === 'category.name') && ($newValue || $deleteOldCat)) {
                        $newValue = array_unique((array)$newValue);
                        $oldValue = !empty($this->_products[$pId][0][$k]) ? (array)$this->_products[$pId][0][$k] : [];

                        $insert1 = array_diff($newValue, $oldValue);
                        $insert = [];
                        $pos = !empty($this->_products[$pId][0]['category.position']) ? max($this->_products[$pId][0]['category.position']) : 0;
                        foreach ($insert1 as $cId) {
                            if (!is_numeric($cId)) {
                                $logger->warning("'$cId' does not seem like category id, skipping.");
                                continue;
                            }
                            if ($this->_categoryProductId($cId)) {
                                $cId = $this->_categoryProductId($cId);
                            }
                            $insert[$cId] = ++$pos;
                        }

                        $delete = $deleteOldCat ? array_diff($oldValue, $newValue) : [];

                        foreach ($delete as $i => $cId) {
                            if ($this->_categoryProductId($cId)) {
                                $delete[$i] = $this->_categoryProductId($cId);
                            }
                        }

                        if ($insert || $delete) {
                            if ($isNew || !$deleteOldCat) {
                                $this->_changeCategoryProduct[$sku]['D'] = [];
                                if (empty($this->_changeCategoryProduct[$sku]['I'])) {
                                    $this->_changeCategoryProduct[$sku]['I'] = [];
                                }
                                foreach ($insert as $cId => $pos) {
                                    $this->_changeCategoryProduct[$sku]['I'][$cId] = $pos;
                                }
                            } else {
                                $this->_changeCategoryProduct[$sku] = array('I' => $insert, 'D' => $delete);
                            }
                            if (!$isNew) {
                                $logger->success(sprintf('new: %s, old: %s',
                                                         implode(', ', $newValue),
                                                         implode(', ', $oldValue)));
                            }
                            $isUpdated = true;
                            $this->addProductForUrlUpdate($sku);
                        }
                        continue;
                    }
                    if (empty($attr['attribute_id']) || empty($attr['backend_type']) || $attr['backend_type'] === 'static') {
                        continue;
                    }
                    // existing attribute values
                    $isValueChanged = false;
                    if ($attr['frontend_input'] === 'media_image' && $newValue) {
                        if ($importImageFiles) {
                            if (!$dryRun) {
                                $this->_currentMediaSku = $sku;
                                $isValueChanged = $this->_copyImageFile($imagesFromDir, $imagesToDir, $newValue, true,
                                                                        $oldValue);
                                if ($isValueChanged === null) {
                                    $isValueChanged = $newValue !== $oldValue;
                                }
                            } else {
                                $isValueChanged = false;
                            }
                        } else {
                            if ($this->_validateImageFile($newValue, $imagesToDir)) {
                                $isValueChanged = $newValue !== $oldValue;
                            } else {
                                $isValueChanged = false;
                            }
                        }
                        if ($isValueChanged) {
                            $this->addProductForImageCacheFlush($sku);
                        }
                        if ($newValue !== $oldValue && !$isNew) {
                            $this->_mediaChanges[$sku . '-' . $k] = [$newValue, $oldValue, $sku];
                        }
                    } elseif (is_array($newValue)) {
                        $oldValue = (array)$oldValue;
                        $isValueChanged = array_diff($newValue, $oldValue) || array_diff($oldValue, $newValue);
                    } else {
                        $isValueChanged = $newValue !== $oldValue;
                    }
                    if (!$isValueChanged && !isset($this->_products[$pId][$storeId][$k]) && $sameAsDefault === 'duplicate') {
                        // if new value is same as old, and old is for default store and duplicate on store level is selected, set value to be changed
                        $isValueChanged = true;
                    }
                    // add updated attribute values
                    $empty = $newValue === '' || $newValue === null || $newValue === [];
                    if (($isNew && !$empty) || $isValueChanged) {
                        #$profile->getLogger()->debug('DIFF', $sku.'/'.$k.': '.print_r($oldValue,1).';'.print_r($newValue,1));
                        $oldValues[$sku][$k] = $oldValue;
                        $this->_changeAttr[$sku][$k] = $newValue;
                        if (!$isNew) {
                            $logger->success(sprintf('new: %s, old: %s',
                                                     is_scalar($newValue) ? $newValue : gettype($newValue),
                                                     is_scalar($oldValue) ? $oldValue : gettype($oldValue)));
                        }
                        if ($storeId && $attr['is_global'] == 2 && !empty($attr['attribute_id'])) {
                            $aId = $attr['attribute_id'];
                            $this->_websiteScope[$sku][$aId] = 1;
                            $this->_websiteScopeAttributes[$aId] = 1;
                            if ($pId) {
                                $this->_websiteScopeProducts[$pId] = 1;
                            }
                        }
                        $__isUpdated = $isUpdated = true;
                    }

                    if ($__isUpdated && $k === 'url_key') {
                        $this->addProductForUrlUpdate($sku);
                    }
                } // foreach ($p as $k=>$newValue)
                if ($forceUrlRewritesRefresh) {
                    $this->addProductForUrlUpdate($sku);
                    $isUpdated = true;
                }

                if ($isUpdated) {
                    $profile->addValue('rows_success');
                    /*
                    $logger->setColumn(0);
                    if (!empty($oldValues[$sku])) $logger->success('OLD: '.print_r($oldValues[$sku],1));
                    if (!empty($this->_changeStock[$sku])) $logger->success('STOCK: '.print_r($this->_changeStock[$sku],1));
                    if (!empty($this->_changeWebsite[$sku])) $logger->success('WEBSITE: '.print_r($this->_changeWebsite[$sku],1));
                    if (!empty($this->_changeAttr[$sku])) $logger->success('ATTR: '.print_r($this->_changeAttr[$sku],1));
                    */
                    if (!$isNew) {
                        $this->_updateEntity[$pId]['updated_at'] = HelperData::now();
                    }
                } else {
                    $profile->addValue('rows_nochange');
                }
            } catch (Row $e) {
                $logger->error($e->getMessage());
                $profile->addValue('rows_error');
            }
        } // foreach ($this->_newData as $p)
        /*
        var_dump($this->_newData);
        echo '<table><tr><td>';
        var_dump($oldValues);
        echo '</td><td>';
        var_dump($this->_changeAttr);
        echo '</td></tr></table>';
        var_dump($this->_changeCategoryProduct);
        var_dump($this->_changeStock);
        var_dump($this->_changeWebsite);
        echo '<hr>';
        */
    }

    protected $_currentMediaSku;

    protected $_mediaProductsProcessed = [];

    /**
     * Get unique image name
     *
     * When importing product images, given the appropriate setting is configured, we need to provide unique image name
     * However the image should be unique compared to other products or previous imports, if this product had same file
     * stored with different name during this import (e.g. importing image, small and thumbnail with same image name)
     * then we should use the same name for all media attributes of the product.
     *
     * @param string $toFilename
     * @return string
     */
    protected function _getUniqueImageName($toFilename)
    {
        // initialize product media cache
        if (empty($this->_mediaProductsProcessed[$this->_currentMediaSku])) {
            $this->_mediaProductsProcessed[$this->_currentMediaSku] = [];
        }

        // if there is no entry for this original file name, get new unique name and store it
        if (!isset($this->_mediaProductsProcessed[$this->_currentMediaSku][$toFilename])) {
            $this->_mediaProductsProcessed[$this->_currentMediaSku][$toFilename] = parent::_getUniqueImageName($toFilename);
        }

        // return unique file name
        return $this->_mediaProductsProcessed[$this->_currentMediaSku][$toFilename];

    }

    protected function _rtIdxRegisterAttrChange($pId, $attrCode, $value, $isSku = true)
    {
        $sku = $pId;
        $pId = $isSku ? $this->_skus[$pId] : $pId;
        $attr = $this->_attr($attrCode);
        if (!empty($attr['rtidx_stock'])) {
            $this->_realtimeIdx['cataloginventory_stock'][$pId] = true;
        }
        if (!empty($attr['rtidx_eav'])) {
            $this->_realtimeIdx['catalog_product_attribute'][$pId] = true;
        }
        if (!empty($attr['rtidx_price'])) {
            $this->_realtimeIdx['catalog_product_price'][$pId] = true;
        }
        if (!empty($attr['rtidx_tag'])) {
            $this->_realtimeIdx['tag_summary'][$pId] = true;
        }
        if (!empty($attr['rtidx_category'])) {
            $this->_realtimeIdx['catalog_category_product'][$pId] = true;
        }
        $excludeActions = ['I', 'D'];
        if (!empty($attr['rtidx_url'])) {
            $this->_rtIdxRegisterByWebsites($sku, $this->_realtimeIdx['catalog_url']['full'], $excludeActions);
        }
        if (!empty($attr['rtidx_search'])) {
            $this->_rtIdxRegisterByWebsites($sku, $this->_realtimeIdx['catalogsearch_fulltext']['full'],
                                            $excludeActions);
        }
        if (in_array($attrCode, $this->_rtIdxFlatAttrCodes)) {
            if ($attrCode === 'status') {
                $this->_rtIdxRegisterByWebsites($sku, $this->_realtimeIdx['catalog_product_flat']['status'][$value],
                                                $excludeActions);
            } else {
                $this->_rtIdxRegisterByWebsites($sku, $this->_realtimeIdx['catalog_product_flat']['by_attr'][$attrCode],
                                                $excludeActions);
            }
        }
    }

    protected function _rtIdxRegisterNewProduct($pId, $isSku = true)
    {
        $sku = $pId;
        $pId = $isSku ? $this->_skus[$pId] : $pId;
        $this->_realtimeIdx['cataloginventory_stock'][$pId] = true;
        $this->_realtimeIdx['catalog_product_attribute'][$pId] = true;
        $this->_realtimeIdx['catalog_product_price'][$pId] = true;
        $this->_realtimeIdx['tag_summary'][$pId] = true;
        $this->_realtimeIdx['catalog_category_product'][$pId] = true;
        $excludeActions = ['I', 'D'];
        $this->_rtIdxRegisterByWebsites($sku, $this->_realtimeIdx['catalog_url']['full'], $excludeActions);
        $this->_rtIdxRegisterByWebsites($sku, $this->_realtimeIdx['catalogsearch_fulltext']['full'], $excludeActions);
        $this->_rtIdxRegisterByWebsites($sku, $this->_realtimeIdx['catalog_product_flat']['full'], $excludeActions);
    }

    protected function _rtIdxRegisterWebsiteChange($pId, $wData, $isSku = true)
    {
        $sku = $pId;
        $pId = $isSku ? $this->_skus[$pId] : $pId;
        $this->_realtimeIdx['cataloginventory_stock'][$pId] = true;
        $this->_realtimeIdx['catalog_product_attribute'][$pId] = true;
        $this->_realtimeIdx['catalog_product_price'][$pId] = true;
        $this->_realtimeIdx['tag_summary'][$pId] = true;
        $this->_realtimeIdx['catalog_category_product'][$pId] = true;
        $excludeActions = ['C'];
        $this->_rtIdxRegisterByWebsites($sku, $this->_realtimeIdx['catalog_url']['website'], $excludeActions);
        $this->_rtIdxRegisterByWebsites($sku, $this->_realtimeIdx['catalogsearch_fulltext']['website'],
                                        $excludeActions);
        $this->_rtIdxRegisterByWebsites($sku, $this->_realtimeIdx['catalog_product_flat']['website'], $excludeActions);
    }

    protected function _rtIdxRegisterCategoryChange($pId, $cData, $isSku = true)
    {
        $sku = $pId;
        $pId = $isSku ? $this->_skus[$pId] : $pId;
        $this->_rtIdxRegisterByWebsites($sku, $this->_realtimeIdx['catalog_url']['full'], ['I', 'D']);
        $this->_realtimeIdx['catalog_category_product'][$pId] = true;
    }

    protected function _rtIdxRegisterStockChange($pId, $sData, $isSku = true)
    {
        $pId = $isSku ? $this->_skus[$pId] : $pId;
        $this->_realtimeIdx['cataloginventory_stock'][$pId] = true;
    }

    protected function _rtIdxRegisterByWebsites($sku, &$indexStorage, $excludeActions = array())
    {
        $pId = $this->_skus[$sku];
        $current = !empty($this->_products[$pId][0]['product.websites'])
            ? $this->_products[$pId][0]['product.websites'] : [];
        $insert = !empty($this->_changeWebsite[$sku]['I'])
            ? $this->_changeWebsite[$sku]['I'] : [];
        $delete = !empty($this->_changeWebsite[$sku]['D'])
            ? $this->_changeWebsite[$sku]['D'] : [];
        $current = array_diff($current, $delete);
        $current = array_unique(array_merge($current, $insert));
        if (!in_array('C', $excludeActions)) {
            foreach ($current as $wId) {
                $indexStorage['C'][$wId][$pId] = true;
            }
        }
        if (!in_array('I', $excludeActions)) {
            foreach ($insert as $wId) {
                $indexStorage['I'][$wId][$pId] = true;
            }
        }
        if (!in_array('D', $excludeActions)) {
            foreach ($delete as $wId) {
                $indexStorage['D'][$wId][$pId] = true;
            }
        }
    }

    protected function _prepareEntityTypeId()
    {
        if ($this->_entityTypeId === null) {
            $this->_entityTypeId = $this->_getEntityType($this->_entityType, 'entity_type_id');
        }

        $this->_prepareEntityIdField();
    }

    protected function _enqueueImageCacheFlush()
    {
        $productsForImageCacheFlush = $this->getProductsForImageCacheFlush();
        $this->resetProductsForImageCacheFlush();
        if(count($productsForImageCacheFlush) === 0){
            return;
        }
        $productIds = [];
        foreach ($productsForImageCacheFlush as $sku) {
            if (!isset($this->_skus[$sku])) {
                $this->_profile->getLogger()->warning($this->__('Product id for %1 not found', $sku));
                continue;
            }
            $productIds[$sku] = $this->_skus[$sku];
        }
        foreach ($productIds as $sku => $productId) {
            $this->_imageCacheHelper->addProductIdForFlushCache($productId);
        }
    }

    private function _enqueueUrlUpdates()
    {
        $productsForUrlUpdates = $this->getProductsForUrlUpdates();
        $this->resetProductsForUrlUpdates();
        if(count($productsForUrlUpdates) === 0){
            return;
        }
        $productIds = [];
        foreach ($productsForUrlUpdates as $sku) {
            if (!isset($this->_skus[$sku])) {
                $this->_profile->getLogger()->warning($this->__('Product id for %1 not found', $sku));
                continue;
            }
            $productIds[$sku] = $this->_skus[$sku];
        }

        $storeId = $this->_profile->getStoreId();
        // reset old values
        $this->_attrValuesFetched[$storeId] = false;
        $this->_products = [];

        $this->_fetchAttributeValues($storeId, false, $productIds);

        foreach ($productIds as $sku => $productId) {
            $visibility = $urlKey = $urlPath = $name = null;
            if(isset($this->_products[$productId][$storeId]['url_key'])){
                $urlKey = $this->_products[$productId][$storeId]['url_key'];
            } else if (isset($this->_products[$productId][0]['url_key'])) {
                $urlKey = $this->_products[$productId][0]['url_key'];
            }
            if(isset($this->_products[$productId][$storeId]['url_path'])){
                $urlPath = $this->_products[$productId][$storeId]['url_path'];
            } else if (isset($this->_products[$productId][0]['url_path'])) {
                $urlPath = $this->_products[$productId][0]['url_path'];
            }
            if(isset($this->_products[$productId][$storeId]['name'])){
                $name = $this->_products[$productId][$storeId]['name'];
            } else if (isset($this->_products[$productId][0]['name'])) {
                $name = $this->_products[$productId][0]['name'];
            }
            if(isset($this->_products[$productId][$storeId]['visibility'])){
                $visibility = $this->_products[$productId][$storeId]['visibility'];
            } else if (isset($this->_products[$productId][0]['visibility'])) {
                $visibility = $this->_products[$productId][0]['visibility'];
            }
            $data = [
                'sku' => $sku,
                'store_id' => $storeId,
                'url_key' => $urlKey,
                'url_path' => $urlPath,
                'name' => $name,
                'visibility' => $visibility
            ];

            if ($this->_rapidFlowHelper->hasMageFeature(self::ROW_ID) && isset($this->_skuSeq[$sku])) {
                $productId = $this->_skuSeq[$sku];
            }

            $this->_urlHelper->addProductIdForRewriteUpdate($productId, $data);
        }
    }

    /**
     * @param $sku
     */
    protected function addProductForUrlUpdate($sku)
    {
        if(!array_key_exists($sku, $this->_urlUpdates)){
            $this->_urlUpdates[$sku] = 1;
        }
    }

    /**
     * @return array
     */
    private function getProductsForUrlUpdates()
    {
        return array_unique(array_keys($this->_urlUpdates));
    }

    protected function resetProductsForUrlUpdates()
    {
        $this->_urlUpdates = [];
    }

    protected function addProductForImageCacheFlush($sku)
    {
        if(!array_key_exists($sku, $this->_mediaUpdates)){
            $this->_mediaUpdates[$sku] = 1;
        }
    }

    protected function getProductsForImageCacheFlush()
    {
        return array_unique(array_keys($this->_mediaUpdates));
    }

    protected function resetProductsForImageCacheFlush()
    {
        $this->_mediaUpdates = [];
    }

    /**
     * @param $usage
     * @param $peakUsage
     * @param $msg
     * @return string
     */
    protected function _systemDebugMessage($usage, $peakUsage, $msg)
    {
        $message = str_pad($msg . ': ' . $usage . ', ' . $peakUsage, 100, '=', STR_PAD_BOTH);

        return $message;
    }

    /**
     * Retrieve proper category ID for product relation
     *
     * In Magento EE with staging feature, categories and products
     * are related by sequence 'entity_id' and not primary key row_id
     *
     * @param int $cId
     * @return int
     */
    protected function _categoryProductId($cId)
    {
        if(isset($this->_categories[$cId]['entity_id'])){
            $cId = $this->_categories[$cId]['entity_id'];
        }

        return $cId;
    }
}
