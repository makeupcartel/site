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
namespace Unirgy\RapidFlow\Model\ResourceModel;

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

use Magento\Eav\Model\Config;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Uploader;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Phrase;
use Magento\Staging\Model\VersionManager;
use Magento\Store\Model\StoreManagerInterface;
use Unirgy\RapidFlow\Exception\Row;
use Unirgy\RapidFlow\Exception\Stop;
use Unirgy\RapidFlow\Helper\Data as HelperData;

/**
 * Class AbstractResource
 * @package Unirgy\RapidFlow\Model\ResourceModel
 */
abstract class AbstractResourceBase extends \Magento\Framework\Model\ResourceModel\AbstractResource
{

    const TABLE_CATALOG_CATEGORY_ENTITY                           = 'catalog_category_entity';
    const TABLE_CATALOG_CATEGORY_PRODUCT                          = 'catalog_category_product';
    const TABLE_CATALOG_EAV_ATTRIBUTE                             = 'catalog_eav_attribute';
    const TABLE_CATALOG_PRODUCT_BUNDLE_OPTION_SEQ                 = 'sequence_product_bundle_option';
    const TABLE_CATALOG_PRODUCT_BUNDLE_OPTION                     = 'catalog_product_bundle_option';
    const TABLE_CATALOG_PRODUCT_BUNDLE_OPTION_VALUE               = 'catalog_product_bundle_option_value';
    const TABLE_CATALOG_PRODUCT_BUNDLE_SELECTION_SEQ              = 'sequence_product_bundle_selection';
    const TABLE_CATALOG_PRODUCT_BUNDLE_SELECTION                  = 'catalog_product_bundle_selection';
    const TABLE_CATALOG_PRODUCT_BUNDLE_SELECTION_PRICE            = 'catalog_product_bundle_selection_price';
    const TABLE_CATALOG_PRODUCT_ENTITY                            = 'catalog_product_entity';
    const TABLE_CATALOG_PRODUCT_ENTITY_GROUP_PRICE                = 'catalog_product_entity_group_price';
    const TABLE_CATALOG_PRODUCT_ENTITY_MEDIA_GALLERY              = 'catalog_product_entity_media_gallery';
    const TABLE_CATALOG_PRODUCT_ENTITY_MEDIA_GALLERY_VALUE        = 'catalog_product_entity_media_gallery_value';
    const TABLE_CATALOG_PRODUCT_ENTITY_MEDIA_GALLERY_VALUE_ENTITY = 'catalog_product_entity_media_gallery_value_to_entity';
    const TABLE_CATALOG_PRODUCT_ENTITY_MEDIA_GALLERY_VALUE_VIDEO  = 'catalog_product_entity_media_gallery_value_video';
    const TABLE_CATALOG_PRODUCT_ENTITY_TIER_PRICE                 = 'catalog_product_entity_tier_price';
    const TABLE_CATALOG_PRODUCT_ENTITY_VARCHAR                    = 'catalog_product_entity_varchar';
    const TABLE_CATALOG_PRODUCT_LINK                              = 'catalog_product_link';
    const TABLE_CATALOG_PRODUCT_LINK_ATTRIBUTE                    = 'catalog_product_link_attribute';
    const TABLE_CATALOG_PRODUCT_LINK_TYPE                         = 'catalog_product_link_type';
    const TABLE_CATALOG_PRODUCT_OPTION                            = 'catalog_product_option';
    const TABLE_CATALOG_PRODUCT_OPTION_PRICE                      = 'catalog_product_option_price';
    const TABLE_CATALOG_PRODUCT_OPTION_TITLE                      = 'catalog_product_option_title';
    const TABLE_CATALOG_PRODUCT_OPTION_TYPE_PRICE                 = 'catalog_product_option_type_price';
    const TABLE_CATALOG_PRODUCT_OPTION_TYPE_TITLE                 = 'catalog_product_option_type_title';
    const TABLE_CATALOG_PRODUCT_OPTION_TYPE_VALUE                 = 'catalog_product_option_type_value';
    const TABLE_CATALOG_PRODUCT_RELATION                          = 'catalog_product_relation';
    const TABLE_CATALOG_PRODUCT_SUPER_ATTRIBUTE                   = 'catalog_product_super_attribute';
    const TABLE_CATALOG_PRODUCT_SUPER_ATTRIBUTE_LABEL             = 'catalog_product_super_attribute_label';
    const TABLE_CATALOG_PRODUCT_SUPER_ATTRIBUTE_PRICING           = 'catalog_product_super_attribute_pricing';
    const TABLE_CATALOG_PRODUCT_SUPER_LINK                        = 'catalog_product_super_link';
    const TABLE_CATALOG_PRODUCT_WEBSITE                           = 'catalog_product_website';
    const TABLE_CATALOGINVENTORY_STOCK_ITEM                       = 'cataloginventory_stock_item';
    const TABLE_INVENTORY_SOURCE                                  = 'inventory_source';
    const TABLE_INVENTORY_STOCK                                   = 'inventory_stock';
    const TABLE_INVENTORY_SOURCE_ITEM                             = 'inventory_source_item';
    const TABLE_INVENTORY_SOURCE_STOCK_LINK                       = 'inventory_source_stock_link';
    const TABLE_INVENTORY_STOCK_SALES_CHANNEL                     = 'inventory_stock_sales_channel';
    const TABLE_CATEGORY_SEQUENCE                                 = 'sequence_catalog_category';
    const TABLE_CUSTOMER_GROUP                                    = 'customer_group';
    const TABLE_DOWNLOADABLE_LINK                                 = 'downloadable_link';
    const TABLE_DOWNLOADABLE_LINK_PRICE                           = 'downloadable_link_price';
    const TABLE_DOWNLOADABLE_LINK_TITLE                           = 'downloadable_link_title';
    const TABLE_DOWNLOADABLE_SAMPLE                               = 'downloadable_sample';
    const TABLE_DOWNLOADABLE_SAMPLE_TITLE                         = 'downloadable_sample_title';
    const TABLE_EAV_ATTRIBUTE                                     = 'eav_attribute';
    const TABLE_EAV_ATTRIBUTE_GROUP                               = 'eav_attribute_group';
    const TABLE_EAV_ATTRIBUTE_LABEL                               = 'eav_attribute_label';
    const TABLE_EAV_ATTRIBUTE_OPTION                              = 'eav_attribute_option';
    const TABLE_EAV_ATTRIBUTE_OPTION_SWATCH                       = 'eav_attribute_option_swatch';
    const TABLE_EAV_ATTRIBUTE_OPTION_VALUE                        = 'eav_attribute_option_value';
    const TABLE_EAV_ATTRIBUTE_SET                                 = 'eav_attribute_set';
    const TABLE_EAV_ENTITY_ATTRIBUTE                              = 'eav_entity_attribute';
    const TABLE_EAV_ENTITY_TYPE                                   = 'eav_entity_type';
    const TABLE_PRODUCT_SEQUENCE                                  = 'sequence_product';
    const TABLE_STORE                                             = 'store';
    const TABLE_STORE_GROUP                                       = 'store_group';
    const TABLE_STORE_WEBSITE                                     = 'store_website';

    /**
     * @var \Unirgy\RapidFlow\Helper\ProtectedCode\Context $context
     */
    protected $_context;

    /**
     *
     */
    const IMPORT_ROW_RESULT_ERROR = 'error';

    /**
     *
     */
    const IMPORT_ROW_RESULT_SUCCESS = 'success';

    /**
     *
     */
    const IMPORT_ROW_RESULT_NOCHANGE = 'nochange';

    /**
     *
     */
    const IMPORT_ROW_RESULT_DEPENDS = 'depends';

    /**
     *
     */
    const IMPORT_ROW_RESULT_EMPTY = 'empty';

    const ROW_ID = 'row_id';
    const BUNDLE_SEQ = 'bundle_seq';
    const SUPER_ATTR_ROW_ID = 'super_attr_row_id';
    const BUNDLE_PARENT = 'bundle_parent';

    /**
     * @var
     */
    protected $_frameworkModelLocale;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var HelperData
     */
    protected $_rapidFlowHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Config
     */
    protected $_eavModelConfig;

    /**
     * @var WriteFactory
     */
    protected $_directoryWriteFactory;

    /**
     * @var ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var
     */
    protected $_exportImageRetainFolders;

    /**
     * @var string
     */
    protected $_translateModule = 'Unirgy_RapidFlow';

    /**
     * @var \Unirgy\RapidFlow\Model\Profile
     */
    protected $_profile;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_res;

    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    protected $_read;

    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    protected $_write;

    /**
     * @var
     */
    protected $_encodingFrom;

    /**
     * @var
     */
    protected $_encodingTo;

    /**
     * @var string
     */
    protected $_encodingIllegalChar;

    /**
     * @var
     */
    protected $_downloadRemoteImages;

    /**
     * @var
     */
    protected $_downloadRemoteImagesBatch;

    /**
     * @var array
     */
    protected $_remoteImagesBatch = [];

    /**
     * @var
     */
    protected $_missingImageAction;

    /**
     * @var
     */
    protected $_existingImageAction;

    /**
     * @var
     */
    protected $_remoteImageSubfolderLevel;

    /**
     * @var
     */
    protected $_imagesMediaDir;

    /**
     * @var
     */
    protected $_deleteOldImage;

    /**
     * @var
     */
    protected $_deleteOldImageSkipUsageCheck;

    /**
     * @var int
     */
    protected $_pageRowCount = 500;

    /**
     * @var int
     */
    protected $_pageSleepDelay = 0;

    protected $_curlConnectTimeout = 5;
    protected $_curlTimeout = 10;
    protected $_curlUserAgent;
    protected $_curlHeaders;
    protected $_curlCustomRequest;

    /**
     * @var FormatInterface
     */
    protected $_locale;

    /**
     *
     * @var string
     */
    protected $_entityIdField = 'entity_id';

    /**
     * DB Table cache
     *
     * @var array
     */
    protected $_tables = [];

    /**
     * DB table names by attribute type
     *
     * @var array
     */
    protected $_tablesByType = [];

    /**
     * Current data row
     *
     * @var array
     */
    protected $_row;

    /**
     * Current row number
     *
     * @var int
     */
    protected $_rowNum;

    /**
     * Current SQL select object
     *
     * @var \Magento\Framework\Db\Select
     */
    protected $_select;

    /**
     * Current filter
     *
     * @var mixed
     */
    protected $_filter;

    /**
     * SKU->ID cache
     *
     * @var array
     */
    protected $_skus = [];

    /**
     * SKU->SEQUENCE ID cache
     *
     * used only with Magento 2 EE v2.1 and up
     * @var array
     */
    protected $_skuSeq;

    /**
     * product SEQUENCE IDs
     *
     * used only with Magento 2 EE v2.1 and up
     * @var array
     */
    protected $_productSeqIds = [];

    /**
     * Mapping of product id to seq id
     * @var array
     */
    protected $_productIdToSeq = [];

    /**
     * Magento EAV configuration singleton
     *
     * @var Config
     */
    protected $_eav;

    /**
     * Limit number of items in cache to avoid memory problems
     *
     * @var mixed
     */
    protected $_maxCacheItems = array(
        'sku' => 10000,
        self::TABLE_CATALOG_PRODUCT_BUNDLE_OPTION => 1000,
        'custom_option' => 1000,
        'custom_option_selection' => 1000,
    );

    /**
     * @var null
     */
    protected $_rootCatId = null;

    /**
     * Cache of dropdown attribute value labels
     *
     * @var array
     */
    protected $_attrOptionsByValue = [];

    /**
     * @var array
     */
    protected $_attrOptionsByLabel = [];

    /**
     * @var array
     */
    protected $_customerGroups = [];

    /**
     * @var array
     */
    protected $_customerGroupsByName = [];

    /**
     * An optional method to call on each row export
     *
     * @var array
     */
    protected $_exportRowCallback = [];

    /**
     * @var array
     */
    protected $_entityTypes = [];

    /**
     * @var array
     */
    protected $_fieldsIdx = [];

    /**
     * @var null
     */
    protected $_storeIds = null;

    /**
     * @var
     */
    protected $_galleryAttrId;

    /**
     * @var array
     */
    protected $_categoryUrlEntities;

    /**
     * @var array
     */
    protected $_attrOptionsStatus;

    /**
     * @var Profile
     */
    protected $_db;

    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     */
    protected $_websiteRepository;

    /**
     * @var \Magento\Store\Api\Data\WebsiteInterface[]
     */
    protected $_websitesById;

    /**
     * @var \Magento\Store\Api\Data\WebsiteInterface[]
     */
    protected $_websitesByCode;
    /**
     * @var \Magento\Staging\Model\VersionManager
     */
    protected $versionManager;
    /**
     * @var \Magento\Staging\Model\Update
     */
    protected $currentVersion;

    protected function _construct()
    {
        /** @var \Unirgy\RapidFlow\Helper\ProtectedCode\Context $context */
        $this->_context = \Magento\Framework\App\ObjectManager::getInstance()->get('\Unirgy\RapidFlow\Helper\ProtectedCode\Context');
        $this->_db = $this->_context->db;
        $this->_res = $this->_db->getResources();
        $this->_read = $this->getConnection();
        $this->_write = $this->getConnection();

        $this->_locale = $this->_context->formatInterface;
        $this->_filesystem = $this->_context->filesystem;
        $this->_rapidFlowHelper = $this->_context->helper;
        $this->_storeManager = $this->_context->storeManager;
        $this->_eavModelConfig = $this->_context->eavConfig;
        $this->_directoryWriteFactory = $this->_context->writeFactory;
        $this->_eventManager = $this->_context->eventManager;
        $this->_websiteRepository = $this->_context->websiteRepository;

        $this->_prepareEntityIdField();
    }

    /**
     * Translate a phrase
     *
     * @return string
     */
    public function __()
    {
        $argc = func_get_args();

        $text = array_shift($argc);
        if (!empty($argc) && is_array($argc[0])) {
            $argc = $argc[0];
        }

        return new Phrase($text, $argc);
    }

    /**
     * @param  \Unirgy\RapidFlow\Model\Profile $profile
     * @return $this
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function setProfile($profile)
    {
        $this->_profile = $profile;
        $profileType = $profile->getProfileType();

        $this->_encodingFrom = $profile->getData('options/encoding/from');
        $this->_encodingTo = $profile->getData('options/encoding/to');
        $this->_encodingIllegalChar = $profile->getData('options/encoding/illegal_char');
        $this->_downloadRemoteImages = $profile->getData('options/' . $profileType . '/image_files_remote');
        $this->_downloadRemoteImagesBatch = $profile->getData('options/' . $profileType . '/image_files_remote_batch');
        $this->_missingImageAction = (string)$profile->getData('options/' . $profileType . '/image_missing_file');
        $this->_existingImageAction = (string)$profile->getData('options/' . $profileType . '/image_existing_file');
        $this->_remoteImageSubfolderLevel = $profile->getData('options/' . $profileType . '/image_remote_subfolder_level');
        $this->_imagesMediaDir = $this->_filesystem->getDirectoryWrite('media')->getAbsolutePath() . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'product';
        $this->_deleteOldImage = $profile->getData('options/' . $profileType . '/image_delete_old');
        $this->_deleteOldImageSkipUsageCheck = $profile->getData('options/' . $profileType . '/image_delete_skip_usage_check');

        return $this;
    }

    /**
     * @return array|mixed|null
     */
    protected function _getStoreIds()
    {
        if (null === $this->_storeIds) {
            $ids = $this->_profile->getData('options/store_ids');
            if (empty($ids)) {
                $this->_storeIds = [];
                return $this->_storeIds;
            }
            if (!is_array($ids)) {
                $ids = explode(',', $ids);
            }
            $this->_storeIds = $ids;
            if ($this->_rapidFlowHelper->hasEeGwsFilter()) {
                $this->_storeIds = $this->_rapidFlowHelper->filterEeGwsStoreIds($this->_storeIds);
            }
        }

        return $this->_storeIds;
    }

    /**
     * Get and validate store ID
     *
     * @param string|int $id
     * @param bool $allowDefault
     * @return int
     * @throws \Exception
     */
    protected function _getStoreId($id, $allowDefault = false)
    {
        $store = $this->_storeManager->getStore($id);
        if (!$store || (!$allowDefault && $store->getId() == 0)) {
            throw new LocalizedException(__('Invalid store'));
        }

        return $store->getId();
    }

    /**
     * @param $id
     * @param bool $allowDefault
     * @return mixed
     * @throws \Exception
     */
    protected function _getWebsiteId($id, $allowDefault = false)
    {
        $website = $this->getWebsite($id);
        if (!$allowDefault && $website->getId() == 0) {
            throw new LocalizedException(__('Invalid website'));
        }

        return $website->getId();
    }

    /**
     * @param int|string $id
     * @return \Magento\Store\Api\Data\WebsiteInterface
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getWebsite($id)
    {
        if(isset($this->_websitesById[$id])){
            return $this->_websitesById[$id];
        } else if(isset($this->_websitesByCode[$id])){
            return $this->_websitesByCode[$id];
        }

        if (is_numeric($id)) {
            $website = $this->_websiteRepository->getById($id);
        } else {
            $website = $this->_websiteRepository->get($id);
        }

        if($website->getId() !== null){
            $this->_websitesById[$website->getId()] = $website;
            $this->_websitesByCode[$website->getCode()] = $website;
        } else {
            throw new LocalizedException(__('Website %1 not found', $id));
        }
        return $website;
    }

    /**
     * @param array $a
     * @param array $b
     * @return bool
     */
    protected function _isChangeRequired(array $a, array $b)
    {
        foreach ($a as $k => $v) {
            if (isset($b[$k]) && $b[$k] != $v) {
                return true;
            }
        }

        return false;
    }

    /**
     * Stop processing if locked
     * @throws Stop
     */
    protected function _checkLock()
    {
        if (!$this->_profile->isLocked()) {
            throw new Stop();
        }
    }

    protected function _prepareEntityIdField()
    {
        if ($this->_rapidFlowHelper->hasMageFeature(self::ROW_ID)) {
            $this->_entityIdField = self::ROW_ID;
            if(isset($this->_fieldAttributes)){
                $this->_fieldAttributes['product.entity_id'] = self::ROW_ID;
                $this->_fieldAttributes['category.entity_id'] = self::ROW_ID;
            }

            $this->versionManager = \Magento\Framework\App\ObjectManager::getInstance()->get(VersionManager::class);
            $this->currentVersion = $this->versionManager->getCurrentVersion();

            $id = $this->currentVersion->getId();
        }
    }

    /**
     * Maintain product SKU->ID cache
     *
     * @param string $sku
     * @return int
     * @throws \Exception
     */
    protected function _getIdBySku($sku)
    {
        // in case we got already resolved id
        if (is_int($sku)) {
            return $sku;
        }
        if (empty($this->_skus[$sku])) {
            $id = $this->_read->fetchOne("SELECT {$this->_entityIdField} FROM {$this->_t(self::TABLE_CATALOG_PRODUCT_ENTITY)} WHERE sku=?",
                                         $sku);
            // keep only last used 10000 skus to avoid memory problems
            if (sizeof($this->_skus) >= $this->_maxCacheItems['sku']) {
                reset($this->_skus);
                unset($this->_skus[key($this->_skus)]);
            }
            $this->_skus[$sku] = $id;
        }
        if (empty($this->_skus[$sku])) {
            throw new LocalizedException(__('Invalid SKU (%1)', $sku));
        }

        return $this->_skus[$sku];
    }

    /**
     * Maintain table name cache
     *
     * @param string $table
     * @return string
     */
    protected function _t($table)
    {
        if (empty($this->_tables[$table])) {
            try {
                $this->_tables[$table] = $this->_res->getTableName($table);
            } catch (\Exception $e) {
                $this->_tables[$table] = false;
            }
        }

        return $this->_tables[$table];
    }

    /**
     * @param $attrCode
     * @param string $entityType
     * @return int|mixed|null
     * @throws \Exception
     */
    protected function _getAttributeId($attrCode, $entityType = 'catalog_product')
    {
        $attr = $this->_eavModelConfig->getAttribute($entityType, $attrCode);
        if (!$attr || !$attr->getAttributeId()) {
            throw new \Exception(__('Invalid attribute: %1', $attrCode));
        }

        return $attr->getAttributeId();
    }

    /**
     * @param $entityTypeCode
     * @param null $field
     * @return mixed
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getEntityType($entityTypeCode, $field = null)
    {
        if (empty($this->_entityTypes[$entityTypeCode])) {
            $entityType = $this->_eavModelConfig->getEntityType($entityTypeCode);
            if (!$entityType) {
                throw new LocalizedException(__('Invalid entity type: %1', $entityTypeCode));
            }
            if (is_object($entityType)) {
                $entityType = $entityType->toArray();
            }
            $this->_entityTypes[$entityTypeCode] = $entityType;
        }

        return !(null === $field) ? $this->_entityTypes[$entityTypeCode][$field] : $this->_entityTypes[$entityTypeCode];
    }

    /**
     * @param $attrCode
     * @param string $entityType
     * @return bool
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _fetchAttributeOptions($attrCode, $entityType = 'catalog_product')
    {
        if (isset($this->_attrOptionsStatus[$attrCode])) {
            return $this->_attrOptionsStatus[$attrCode];
        }
        $attr = $this->_eavModelConfig->getAttribute($entityType, $attrCode);
        if (!$attr) {
            throw new \Exception(__('Invalid attribute: %1', $attrCode));
        }
        $aId = $attr->getAttributeId();
        if (!isset($this->_attrOptionsByValue[$aId])) {
            if (!$attr->usesSource()) {
                $this->_attrOptionsStatus[$attrCode] = false;

                return false;
            }
            $options = $attr->getSource()->getAllOptions();
            foreach ($options as $o) {
                if (!$o['value']) {
                    continue;
                }
                $this->_attrOptionsByValue[$aId][$o['value']] = $o['label'];
                $this->_attrOptionsByLabel[$aId][strtolower($o['label'])] = $o['value'];
            }
        }
        $this->_attrOptionsStatus[$attrCode] = true;

        return true;
    }

    /**
     * Apply product filter...
     * @param string $attr
     */
    protected function _applyProductFilter($attr = 'main.entity_id')
    {
        $attr = str_replace('entity_id', $this->_entityIdField, $attr);
        if (!empty($this->_filter['product_ids'])) {
            $this->_select->where("{$attr} in (?)", $this->_filter['product_ids']);
        }
        $productIds = $this->_profile->getConditionsProductIds();
        if (is_array($productIds)) {
            $this->_select->where("{$attr} in (?)", $productIds);
        }
    }

    /**
     * @param $key
     * @param bool $byName
     * @return mixed
     * @throws Row
     */
    protected function _getCustomerGroup($key, $byName = false)
    {
        if (!$this->_customerGroups) {
            $rows = $this->_read->fetchAll("select * from {$this->_t('customer_group')}");
            $this->_customerGroups = [];
            foreach ($rows as $r) {
                $this->_customerGroups[$r['customer_group_id']] = $r['customer_group_code'];
                $this->_customerGroupsByName[strtolower($r['customer_group_code'])] = $r['customer_group_id'];
            }
        }
        $errorMsg = __('Invalid customer group: %1', $key);
        if ($byName) {
            if (!isset($this->_customerGroupsByName[strtolower($key)])) {
                throw new Row($errorMsg);
            }

            return $this->_customerGroupsByName[strtolower($key)];
        } else {
            if (!isset($this->_customerGroups[$key])) {
                throw new Row($errorMsg);
            }

            return $this->_customerGroups[$key];
        }
    }

    /**
     * @return string
     */
    protected function _getGalleryAttrId()
    {
        if (!$this->_galleryAttrId) {
            $this->_galleryAttrId = $this->_write->fetchOne("select attribute_id from {$this->_t('eav_attribute')} where attribute_code='media_gallery' and frontend_input='gallery'");
        }

        return $this->_galleryAttrId;
    }

    /**
     * @var array map of urls to local file names
     */
    protected $_remoteImagesCache = [];

    /**
     * @param $fromDir
     * @param $toDir
     * @param $filename
     * @param bool $import
     * @param null $oldValue
     * @param bool $noCopyFlag
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws Row
     */
    protected function _copyImageFile(
        $fromDir,
        $toDir,
        &$filename,
        $import = false,
        $oldValue = null,
        $noCopyFlag = false
    ) {
        $ds = '/';

        $remote = preg_match('#^https?:#', $filename);
        $isRemoteBatch = $remote && $this->_downloadRemoteImagesBatch;
        if ($remote && !$this->_downloadRemoteImages) {
            // when image is remote, and remote images are not allowed, do nothing and reset imported value
            $this->_profile->getLogger()->warning($this->__('Skipping: %1, remote images download is disabled.',
                                                            $filename));
            $this->_profile->addValue('num_warnings');
            $filename = '';
            return true;
        }

        $basename = basename($filename);
        /*
        if ($remote) {
            $basename = basename(parse_url($filename, PHP_URL_PATH));
        }
        */

        $fromDir = rtrim($fromDir, '/\\');
        $toDir = rtrim($toDir, '/\\');
        if (null === $this->_exportImageRetainFolders) {
            $this->_exportImageRetainFolders = $this->_profile->getData('options/export/image_retain_folders')?:false;
        }
        if (!$import && $this->_exportImageRetainFolders) {
            $prefix = substr($filename, 0, -strlen($basename));
            $toDir = $toDir . $ds . trim($prefix, '/\\');
        }

        if ($import && $remote) {
            $slashPos = false;
            $fromFilename = $filename;
            $fromExists = true;
            $fromRemote = true;
            // if remote image and it has been already downloaded, use the existing file instead of downloading
            if (isset($this->_remoteImagesCache[$fromFilename])) {
                $filename = $this->_remoteImagesCache[$fromFilename]['name'];
                $this->_profile->getLogger()->warning($this->__('%1 is downloaded already, using local file: %2.',
                                                                $fromFilename, $filename));
                $fromFilename = $this->_remoteImagesCache[$fromFilename]['path'];
                $fromRemote = false;
                $fromExists = $isRemoteBatch || is_readable($fromFilename);
                $slashPos = strpos($filename, $ds);
            } else {  // remote file is not yet downloaded
                if ($this->_remoteImageSubfolderLevel) {
                    $filenameArr = explode('/', $filename);
                    array_pop($filenameArr);
                    $filename = $basename;
                    for ($i = 0; $i < $this->_remoteImageSubfolderLevel; $i++) {
                        $filename = array_pop($filenameArr) . $ds . $filename;
                    }
                    $slashPos = strpos($filename, $ds);
                } else {
                    $filename = $basename;
                }
                $filename = str_replace(
                    [' ', '%', '?', '/'],
                    '-',
                    urldecode($filename));
            }
        } else {
            $slashPos = strpos($filename, $ds);
            $fromFilename = $fromDir . $ds . ltrim($filename, $ds);
            /*
            if ($import && $slashPos===0) {
                // if importing and filename starts with slash, use only basename for source file
                $fromFilename = $fromDir.$ds.basename($filename);
            }
            */
            $fromExists = is_readable($fromFilename);
            $fromRemote = false;
        }

        if (is_dir($fromFilename)) {
            // swatch images are media type attribute but do not have actual image most of the time
            $this->_profile->getLogger()->warning(__('%1 is not valid file, skipping copy', $fromFilename));
            return true;
        }

        $warning = '';
        //error_log('4', 3, '/var/www/html/var/log/unirgy.log');
        $origBasename = basename($filename);
        $cleanBasename = \Magento\Framework\File\Uploader::getCorrectFileName($origBasename);
        if ($origBasename!=$cleanBasename) {
            $filename = str_replace($origBasename, $cleanBasename, $filename);
            $warning .= __(' Corrected image name: %1.', $filename);
        }
        //$cleanFilename = $filename;
        $toFilename = $toDir . $ds . ltrim($filename, $ds);
        if ($import) {
            if ($slashPos === false) {
                $prefix = str_replace('\\', $ds, Uploader::getDispretionPath($filename));
                $toDir .= $ds . ltrim($prefix, $ds);
                $toFilename = rtrim($toDir, $ds) . $ds . ltrim($filename, $ds);
                $filename = $prefix . $ds . $filename;
            } elseif ($dirname = dirname($filename)) {
                $toDir .= $ds . ltrim($dirname, $ds);
            }
        } elseif (!$import && $slashPos === 0) {
            $toFilename = $toDir . $ds . basename($filename);
        }
        $toExists = is_readable($toFilename);

        $filename = $ds . ltrim($filename, $ds);

        if ($noCopyFlag) {
            return true;
        }

        if ($import && $toExists && $this->_existingImageAction) {
            $this->_profile->addValue('num_warnings');
            $warning .= __('Imported image file already exists.');
            if ($filename === $oldValue) {
                // new file name is same as current value
                $warning .= $this->__(' %1 is same as current value, %2.', $filename, $oldValue);
            } else {
                switch ($this->_existingImageAction) {
                    case 'skip':
                        $warning .= __(' Skipping field update');
                        $this->_profile->getLogger()->warning($warning);

                        return false;
                        break;
                    case 'replace' :
                        // basically just notify user that there is
                        $warning .= __(' Replacing existing image');
                        break;
                    case 'save_new':
                        $warning     .= __(' Updating image name and saving as new image.');
                        $toFilename  = $this->_getUniqueImageName($toFilename);
                        $newBasename = basename($toFilename);
                        $oldBasename = basename($filename);
                        if ($newBasename !== $oldBasename) {
                            $filename = str_replace($oldBasename, $newBasename, $filename);
                            $warning  .= __(' New image name: %1', $filename);
                        }
                        break;
                }
            }
            $this->_profile->getLogger()->warning($warning);
        } else if ($import && !$toExists) {
            // if have to import, but image is new
            $this->_getUniqueImageName($toFilename);
        }

        if (!$fromExists) {
            $warning = __('Source image file does not found: %1', $fromFilename);
//            $warning = __('Original file image does not exist');
            if ($this->_missingImageAction === 'error') {
                throw new Row($warning);
            } else {
                $result = false;
                switch ($this->_missingImageAction) {
                    case '':
                    case 'warning_save':
                        $result = true;
                        $warning .= '. ' . PHP_EOL . __('Image field set to: %1', $filename);
                        break;

                    case 'warning_skip':
                        $warning .= '. ' . __('Image field was not updated');
                        $filename = $oldValue; // set import value to be same as old value and avoid update
                        break;

                    case 'warning_empty':
                        $warning .= '. ' . __('Image field was reset');
                        $filename = '';
                        $result = true;
                        break;
                }
                $this->_profile->addValue('num_warnings');
                $this->_profile->getLogger()->warning($warning);

                return $result;
            }
        } elseif ($toExists && $fromExists && !$fromRemote
            && $filename === $oldValue
            && filesize($fromFilename) === filesize($toFilename)
        ) {
            // no need to copy
            return false;
        }

        $this->_directoryWriteFactory->create($toDir)->create();

        if ($fromRemote) {
            $error = null;
            try {
                if (!function_exists('curl_init')) {
                    throw new \Exception(__('Unable to locate curl module'));
                }
                if (!$this->_downloadRemoteImagesBatch) {
                    if (!($ch = curl_init($fromFilename))) {
                        throw new \Exception(__('Unable to open remote file: %1', $fromFilename));
                    }
                    if ($this->_curlCustomRequest) curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->_curlCustomRequest);
                    if ($this->_curlUserAgent) curl_setopt($ch, CURLOPT_USERAGENT, $this->_curlUserAgent);
                    if ($this->_curlHeaders) {
                        curl_setopt($ch, CURLOPT_HEADER, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_curlHeaders);
                    }
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->_curlConnectTimeout);
                    curl_setopt($ch, CURLOPT_TIMEOUT, $this->_curlTimeout);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
                    curl_setopt($ch, CURLOPT_NOBODY, 1);
                    curl_setopt($ch, CURLOPT_HEADER, 1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $headResult = curl_exec($ch);
                    if ($headResult === false) {
                        throw new \Exception(__('Testing for remote file "%1" fails', $fromFilename));
                    }
                    if (false !== strpos($headResult, '404 Not Found')) {
                        throw new \Exception(__('"404 Not Found" response for remote file: %1', $fromFilename));
                    }
                    if (!($fp = fopen($toFilename, 'w'))) {
                        throw new \Exception(__('Unable to open local file for writing: %1', $toFilename));
                    }
                    curl_setopt($ch, CURLOPT_NOBODY, 0);
                    curl_setopt($ch, CURLOPT_HTTPGET, 1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
                    curl_setopt($ch, CURLOPT_FILE, $fp);
                    curl_setopt($ch, CURLOPT_HEADER, 0);

                    if (!curl_exec($ch)) {
                        throw new \Exception(__('Unable to fetch remote file: %1', $fromFilename));
                    }
                } else {
                    $this->_remoteImagesBatch[$fromFilename] = $toFilename;
                }
                $this->_remoteImagesCache[$fromFilename]['name'] = $filename;
                $this->_remoteImagesCache[$fromFilename]['path'] = $toFilename;
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
            if (!empty($ch)) {
                curl_close($ch);
            }
            if (!empty($fp)) {
                fclose($fp);
            }
            if (!$this->_isValidImageFile($toFilename)) {
                @unlink($toFilename);
                unset($this->_remoteImagesCache[$fromFilename]);
                $error = __('Remote file is not an image: %1', $fromFilename);
            }

            if (!empty($error)) {
                $this->_profile->addValue('num_warnings');
                $this->_profile->getLogger()->warning($error);

                return false;
            }
        } else {
            if ($fromFilename === $toFilename && ($isRemoteBatch || filesize($fromFilename) === filesize($toFilename))) {
                return true; // do not try to copy same image over itself
            }
            if($toExists){
                @unlink($toFilename);
            }
            if (!@copy($fromFilename, $toFilename)) {
                $errors = error_get_last();
                $error = 'COPY ERROR: ';
                if ($errors && array_key_exists('type', $errors)) {
                    $error .= $errors['type'];
                }
                if ($errors && array_key_exists('message', $errors)) {
                    $error .= PHP_EOL . $errors['message'];
                }
                $this->_profile->addValue('num_warnings');
                $this->_profile->getLogger()->warning(__('Was not able to copy image file: %1', $error));

                return false;
            }
        }
        $eventVars = [
            'basename' => $basename,
            'filename' => $filename,
            'from_dir' => $fromDir,
            'from_filename' => $fromFilename,
            'from_remote' => $fromRemote,
            'to_dir' => $toDir,
            'to_exists' => $toExists,
            'import' => $import,
            'profile' => $this->_profile,
            'old_value' => $oldValue,
        ];

        $this->_eventManager->dispatch('urapidflow_copy_image_file_success', $eventVars);

        return true;
    }

    protected function _isValidImageFile($filename)
    {
        $validator = new \Zend\Validator\File\MimeType('image/png,image/jpeg,image/pjpeg,image/gif');
        return $validator->isValid($filename);
    }

    protected function _importProcessRemoteImageBatch()
    {

        if (!$this->_remoteImagesBatch) {
            return;
        }
        $t = microtime(1);
        $mh = curl_multi_init();
        $files = [];
        $handles = [];
        foreach ($this->_remoteImagesBatch as $fromFilename => $toFilename) {
            try {
                if (!($ch = curl_init($fromFilename))) {
                    throw new \Exception(__('Unable to open remote file: %1', $fromFilename));
                }
                if (!($fp = fopen($toFilename, 'w'))) {
                    throw new \Exception(__('Unable to open local file for writing: %1', $toFilename));
                }
                //error_log("STARTED: {$fromFilename} => {$toFilename}\n", 3, '/var/www/html/var/log/unirgy.log');
                if ($this->_curlCustomRequest) curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->_curlCustomRequest);
                if ($this->_curlUserAgent) curl_setopt($ch, CURLOPT_USERAGENT, $this->_curlUserAgent);
                if ($this->_curlHeaders) {
                    curl_setopt($ch, CURLOPT_HEADER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_curlHeaders);
                }
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->_curlConnectTimeout);
                curl_setopt($ch, CURLOPT_TIMEOUT, $this->_curlTimeout);
                curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.5.4');
                curl_setopt($ch, CURLOPT_NOBODY, 0);
                curl_setopt($ch, CURLOPT_HTTPGET, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                $files[] = $fp;
                $handles[] = $ch;
                curl_multi_add_handle($mh, $ch);
            } catch (\Exception $e) {
                $this->_profile->getLogger()->warning($e->getMessage());
            }
        }

        $active = null;
        do {
            $mrc = curl_multi_exec($mh, $active);
            //error_log('1', 3, '/var/www/html/var/log/unirgy.log');
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($mh) == -1) {
                usleep(100);
            }
            do {
                $mrc = curl_multi_exec($mh, $active);
                //error_log('2', 3, '/var/www/html/var/log/unirgy.log');
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }

        foreach ($handles as $ch) {
            curl_multi_remove_handle($mh, $ch);
            //error_log('0', 3, '/var/www/html/var/log/unirgy.log');
        }
        curl_multi_close($mh);
        foreach ($files as $fp) {
            fclose($fp);
        }
        //error_log("\nTOTAL TIME: " . (microtime(1) - $t) . "\n", 3, '/var/www/html/var/log/unirgy.log');
        $this->_remoteImagesBatch = [];
    }

    /**
     * @param string $toFilename
     *
     * @return mixed|string
     */
    protected function _getUniqueImageName($toFilename)
    {
        $fileInfo = pathinfo($toFilename);
        $newName = Uploader::getNewFileName($toFilename);
        $extension = isset($fileInfo['extension'])? '.' . $fileInfo['extension']: '';
        $toFilename = str_replace($fileInfo['filename'] . $extension, $newName, $toFilename);

        return $toFilename;
    }

    /**
     * @param $filename
     * @param $toDir
     * @param bool $noCopyFlag
     * @return bool
     * @throws Row
     */
    protected function _validateImageFile(&$filename, $toDir, $noCopyFlag = false)
    {
        $ds = '/';
        if (($slashPos = strpos($filename, $ds)) !== false) {
            $filename = ltrim($filename, $ds);
        }
        $result = false;
        if (file_exists($toDir . $ds . $filename) && !is_dir($toDir . $ds . $filename)) {
            $filename = $ds . ltrim($filename, $ds);
            $result = true;
        } elseif ($slashPos === false) {
            $prefix = str_replace('\\', $ds, Uploader::getDispretionPath($filename));
            $tempFilename = $ds . trim($prefix, $ds) . $ds . ltrim($filename, $ds);
            if (file_exists($toDir . $tempFilename) && !is_dir($toDir . $tempFilename)) {
                $filename = $tempFilename;
                $result = true;
            }
        } else {
            $filename = $ds . ltrim($filename, $ds);
        }

        if ($result || $noCopyFlag) {
            return $result;
        }

        $warning = __('Related file image is not in destination media folder');
        if ($this->_missingImageAction === 'error') {
            throw new Row($warning);
        }
        $result = false;
        switch ($this->_missingImageAction) {
            case '':
            case 'warning_save':
                $result = true;
                break;

            case 'warning_skip':
                $warning .= '. ' . __('Image field was not updated');
                break;

            case 'warning_empty':
                $warning .= '. ' . __('Image field was reset');
                $filename = null;
                $result = true;
                break;
        }
        $this->_profile->addValue('num_warnings');
        $this->_profile->getLogger()->warning($warning);

        return $result;
    }

    /**
     * @param string $value
     * @return array|string
     * @throws \Exception
     */
    protected function _convertEncoding($value)
    {
        if ($value && $this->_encodingFrom && $this->_encodingTo && $this->_encodingFrom != $this->_encodingTo) {
            /*
            $from = $this->_encodingFrom;
            if ($this->_encodingFrom=='auto') {
                $from = mb_detect_encoding($value.'a', 'auto');
                if (!$from) {
                    $from = 'UTF-8';
                }
            }
            */
            if (is_array($value)) {
                foreach ($value as $i => $v) {
                    $value[$i] = $this->_convertEncoding($v);
                }
            } else {
                $encodingTo = $this->_encodingTo . ($this->_encodingIllegalChar ? '//' . $this->_encodingIllegalChar : '');
                try {
                    $value1 = iconv($this->_encodingFrom, $encodingTo, $value);
                } catch (\Exception $e) {
                    if (strpos($e->getMessage(), 'Detected an illegal character in input string') !== false) {
                        $this->_profile->addValue('num_warnings');
                        $this->_profile->getLogger()->warning(__('Illegal character in string: %1', $value));
                        $value1 = $value;
                    } else {
                        throw $e;
                    }
                }
                $value = $value1;
            }
        }

        return $value;
    }

    /**
     * @param $pIds
     * @param bool $useKeys
     * @return $this
     */
    protected function _refreshHasOptionsRequiredOptions($pIds, $useKeys = true)
    {
        if (!empty($pIds)) {
            $entityId = $this->_entityIdField;

            if ($useKeys) {
                $pIds = array_keys($pIds);
            }
            $horoSelect = $this->_write->select()
                ->from(['p' => $this->_t(self::TABLE_CATALOG_PRODUCT_ENTITY)], [$entityId])
                ->joinLeft(['po' => $this->_t(self::TABLE_CATALOG_PRODUCT_OPTION)],
                           'po.product_id=p.' . $entityId, [])
                ->where("p.{$entityId} in (?)", $pIds)
                ->group("p.{$entityId}")
                ->columns('sum(IF(po.option_id is not null, 1, 0)) as has_options');
            $horoSelect->columns('sum(IF(po.option_id is not null and po.is_require!=0, 1, 0)) as required_options');
            $horoRows = $this->_write->fetchAll($horoSelect);

            $horoSelect = $this->_write->select()
                ->from(['p' => $this->_t(self::TABLE_CATALOG_PRODUCT_ENTITY)], [$entityId])
                ->joinLeft(['po' => $this->_t(self::TABLE_CATALOG_PRODUCT_SUPER_ATTRIBUTE)],
                           "po.product_id=p.{$entityId}", [])
                ->where("p.{$entityId} in (?)", $pIds)
                ->where('p.type_id=?', 'configurable')
                ->group("p.{$entityId}")
                ->columns('sum(IF(po.product_super_attribute_id is not null, 1, 0)) as has_options');
            $horoSelect->columns('sum(IF(po.product_super_attribute_id is not null, 1, 0)) as required_options');

            foreach ($this->_write->fetchAll($horoSelect) as $horo) {
                foreach ($horoRows as &$_horo) {
                    if ($_horo[$entityId] == $horo[$entityId]) {
                        $_horo['has_options'] = max($_horo['has_options'], $horo['has_options']);
                        $_horo['required_options'] = max($_horo['required_options'], $horo['required_options']);
                        break;
                    }
                }
                unset($_horo);
            }

            $horoSelect = $this->_write->select()
                ->from(['p' => $this->_t(self::TABLE_CATALOG_PRODUCT_ENTITY)], [$entityId])
                ->joinLeft(['po' => $this->_t(self::TABLE_CATALOG_PRODUCT_BUNDLE_OPTION)], "po.parent_id=p.{$entityId}",
                           [])
                ->where("p.{$entityId} in (?)", $pIds)
                ->where('p.type_id=?', 'bundle')
                ->group("p.{$entityId}")
                ->columns('sum(IF(po.option_id is not null, 1, 0)) as has_options');
            $horoSelect->columns('sum(IF(po.option_id is not null and po.required!=0, 1, 0)) as required_options');
            foreach ($this->_write->fetchAll($horoSelect) as $horo) {
                foreach ($horoRows as &$_horo) {
                    if ($_horo[$entityId] == $horo[$entityId]) {
                        $_horo['has_options'] = max($_horo['has_options'], $horo['has_options']);
                        $_horo['required_options'] = max($_horo['required_options'], $horo['required_options']);
                        break;
                    }
                }
                unset($_horo);
            }

            $query = 'UPDATE ' . $this->_t(self::TABLE_CATALOG_PRODUCT_ENTITY) . ' SET ';
            $hoSql = "`has_options`=CASE `{$entityId}`";
            $roSql = '';
            $roSql = ", `required_options`=CASE `{$entityId}`";
            foreach ($horoRows as $horo) {
                $hoSql .= $this->_write->quoteInto(' WHEN ? ', $horo[$entityId]);
                $hoSql .= $this->_write->quoteInto(' THEN ? ', $horo['has_options'] > 0 ? 1 : 0);
                $roSql .= $this->_write->quoteInto(' WHEN ? ', $horo[$entityId]);
                $roSql .= $this->_write->quoteInto(' THEN ? ', $horo['required_options'] > 0 ? 1 : 0);
            }
            $hoSql .= ' ELSE `has_options` END';
            $roSql .= ' ELSE `required_options` END';

            $query .= $hoSql;
            $query .= $roSql;
            $query .= $this->_write->quoteInto(" WHERE `{$entityId}` IN (?)", $pIds);

            $this->_write->query($query);

        }

        return $this;
    }

    /**
     * @param array $row
     * @param string $dataKey
     * @param string $parentKey
     * @return string
     * @throws LocalizedException
     */
    protected function catBuildPath($row, $dataKey = 'url_key', $parentKey = 'url_key')
    {
        $path = null;
        $eId = isset($row[$this->_entityIdField]) ? $row[$this->_entityIdField] : null;
        $rcID = $this->_getRootCatId();
        $rootPath = $rcID ? '1/' . $rcID . '/' : '1/';
        $entities = $this->_getCategoryUrlEntities();
        if (!empty($row[$dataKey])) {
            //$rootPath    = $this->_rootCatId ? '1/' . $this->_rootCatId . '/' : '1/';
            $ancestorIds = explode('/', str_replace($rootPath, '', $row['path']));
            $eId = array_pop($ancestorIds); // remove current cat id
            $urlKeys = [];
            if ($row['path'].'/'!=$rootPath) {
                foreach ($ancestorIds as $aid) {
                    if (!isset($entities[$aid])) {
                        // maybe it is EE
                        if ($this->_categoriesBySeqId && isset($this->_categoriesBySeqId[$aid])) {
                            $aid = $this->_categoriesBySeqId[$aid][$this->_entityIdField];
                        }

                        if (!isset($entities[$aid])) {
                            $this->_profile->getLogger()
                                ->warning(sprintf('Parent category with id: %s not found. Category id: %s', $aid,
                                    $eId));

                            return $path;
                        }
                    }
                    $ancestor = $entities[$aid];
                    $urlKeys[] = isset($ancestor[$parentKey]) ? $ancestor[$parentKey] : @$ancestor[0][$parentKey];
                }
            }
            $urlKeys[] = $row[$dataKey];
            if (!empty($urlKeys)) {
                $path = implode("/", $urlKeys);
            }
        } elseif (!(!empty($row['path']) && $row['path'] === '1' || $row['path'] === "1/{$rcID}")) {
            $this->_profile->getLogger()->warning(sprintf('Category: %s is missing url_key: ' . print_r($row, 1) . ', '. $rootPath, $eId ?: 'N/A'));
        }

        return $path;
    }

    /**
     * @return null|string
     */
    protected function _getRootCatId()
    {
        if (null === $this->_rootCatId) {
            $storeId = $this->_profile->getStoreId();
            if ($storeId) {
                $this->_rootCatId = $this->_storeManager->getStore($storeId)->getGroup()->getRootCategoryId();
            } else {
                $this->_rootCatId = $this->_read->fetchOne("SELECT g.root_category_id FROM {$this->_t('store_website')} w INNER JOIN {$this->_t('store_group')} g ON g.group_id=w.default_group_id WHERE w.is_default=1");
            }
        }

        return $this->_rootCatId;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getCategoryUrlEntities()
    {
        if (!$this->_categoryUrlEntities) {
            $eav = $this->_eavModelConfig;
            $categories = [];
            $storeId = $this->_profile->getStoreId();
            $table = $this->_t('catalog_category_entity');
            foreach (array('url_key', 'url_path') as $k) {
                $attribute = $eav->getAttribute('catalog_category', $k);
                $attrId = $attribute->getAttributeId();
                // fetch attribute values for all categories
                $tableName = $attribute->getBackendTable() ?: $table . '_varchar';
                $sql = "SELECT {$this->_entityIdField}, value FROM {$tableName} WHERE attribute_id={$attrId} AND store_id IN (0, {$storeId}) ORDER BY store_id DESC";
                foreach ($this->_read->fetchAll($sql) as $r) {
                    // load values for specific store OR default
                    if (empty($categories[$r[$this->_entityIdField]][$k])) {
                        $categories[$r[$this->_entityIdField]][$k] = $r['value'];
                    }
                }
            }
            $this->_categoryUrlEntities = $categories;
        }

        return $this->_categoryUrlEntities;
        // todo, fetch data with category entity_id, url_key, url_path columns
    }

    /**
     * @param array $attr
     * @param string $baseTableAbstract
     * @return string
     */
    protected function getAttrType($attr, $baseTable = "catalog_product_entity")
    {
        $type = $attr['backend_type'];
        $baseTable = $this->_t($baseTable);
        if (!empty($attr['backend_table'])) {
            $attrTable = $this->_t($attr['backend_table']);
            $diff = str_ireplace($baseTable . "_", "", $attrTable);
            if (!empty($diff)) {
                $type = $diff;
            }
        }
        if (empty($this->_tablesByType[$type])) {
            $this->_tablesByType[$type] = !empty($attrTable) ? $attrTable : "{$baseTable}_{$attr['backend_type']}";
        }
        return $type;
    }


    protected function _getNextProductSequence($dryRun = false)
    {
        $tableName = $this->_t(static::TABLE_PRODUCT_SEQUENCE);
        return !$dryRun? $this->_getNextSequence($tableName): 1;
    }

    protected function _getNextCategorySequence($dryRun = false)
    {
        $tableName = $this->_t(static::TABLE_CATEGORY_SEQUENCE);
        return !$dryRun ? $this->_getNextSequence($tableName) : 1;
    }

    abstract public function import();

    abstract public function export();

    /**
     * @param string $tableName
     * @return string
     * @throws \Zend_Db_Adapter_Exception
     */
    protected function _getNextSequence($tableName)
    {
        $this->_write->insert($tableName, []);
        return $this->_write->lastInsertId($tableName);
    }
}
