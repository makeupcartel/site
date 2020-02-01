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

namespace Unirgy\RapidFlow\Helper;

use Magento\Catalog\Model\Product\Url as ProductUrl;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Observer\UrlRewriteHandler;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Logger\Monolog;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Unirgy\RapidFlow\Model\Profile;
use Unirgy\RapidFlow\Model\ResourceModel\AbstractResource;
use Unirgy\RapidFlow\Model\ResourceModel\Catalog\Product as RfProduct;
use Magento\Framework\Module\ModuleListInterface;
use Unirgy\RapidFlow\Model\ResourceModel\Catalog\Product;
use Magento\Framework\DB\Adapter\AdapterInterface;

class Data extends AbstractHelper
{
    /**
     * @var Monolog
     */
    protected static $_customLog;
    /**
     * @var Profile
     */
    protected $_profile;

    /**
     * @var ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var ProductUrl
     */
    protected $_productUrl;

    /**
     * @var bool
     */
    protected $_ee_gws_filter;

    /**
     * @var array
     */
    protected $_moduleActive;
    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $modulesList;
    /**
     * @var array
     */
    protected $_hasMageFeature = [];
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /** @var CategoryUrlRewriteGenerator */
    protected $categoryUrlRewriteGenerator;

    /** @var UrlPersistInterface */
    protected $urlPersist;

    /** @var UrlRewriteHandler */
    protected $urlRewriteHandler;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var array
     */
    protected $_categoryIdsToUpdate = [];
    protected $_categorySeqIdsToUpdate = [];

    public function __construct(
        Context $context,
        ManagerInterface $messageManager,
        PageFactory $pageFactory,
        StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    )
    {
        $this->_messageManager = $messageManager;
        $this->_pageFactory = $pageFactory;
        $this->_storeManager = $storeManager;

        $this->categoryFactory = $categoryFactory;

        parent::__construct($context);
    }

    protected function _backendAuth()
    {
        return ObjectManager::getInstance()->get('\Magento\Backend\Model\Auth');
    }

    protected static $metadata;

    /**
     * @return ProductMetadataInterface|null
     * @throws \RuntimeException
     */
    protected static function _getMetaData()
    {
        if (null === self::$metadata) {
            self::$metadata = self::om()->get(ProductMetadataInterface::class);
        }
        return self::$metadata;
    }

    /**
     * Run a profile by its name or id
     *
     * If $stopIdRunning is true, profile will be restarted if already running
     * You can update profile data by passing replacement data in $updateData parameter
     *
     * @param int|string $profileId
     * @param bool $stopIfRunning
     * @param array $updateData
     * @return Profile
     * @throws \Exception
     * @api
     */
    public function run($profileId, $stopIfRunning = true, array $updateData = [])
    {
        $profile = $this->getProfile($profileId);

        if ($stopIfRunning) {
            try {
                $profile->stop();
            } catch (\Exception $e) {
                $this->_logger->error($e->getMessage());
            }
        }

        if (!empty($updateData)) {
            foreach ($updateData as $k => $v) {
                if (is_array($v)) {
                    $profile->setData($k, array_merge_recursive($profile->getData($k), $v));
                } else {
                    $profile->setData($k, $v);
                }
            }
        }

        $profile->start()->save()->run();

        return $profile;
    }

    /**
     * Retrieve profile by its ID or title
     *
     * @param int|string $profileId profile id or profile title
     * @return Profile
     * @throws \Exception
     */
    public function getProfile($profileId)
    {
        /* @var $profile Profile */
        $profile = self::om()->create(Profile::class);

        if (is_numeric($profileId)) {
            $profile->load($profileId);
        } else {
            $profile->load($profileId, 'title');
        }

        if (!$profile->getId()) {
            $this->_messageManager->addError(__('Invalid Profile ID'));
        }

        $profile = $profile->factory();
        return $profile;
    }

    public function addAdminhtmlVersion($module = 'Unirgy_RapidFlow')
    {
        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $this->_pageFactory->create()->getLayout();
        $version = $this->getModuleList()->getOne($module);
        if (!empty($version)) {
            $version = $version['setup_version'];
        }
        /** @var \Magento\Framework\View\Element\Text $block */
        $block = $layout->addBlock('Magento\Framework\View\Element\Text', $module . '.version', 'before.body.end');

        $block->setText('<script type="text/javascript">
            require([\'jquery\'], function($){
                $(".footer-legal").append("' . $module . ' ver. ' . $version . ',");            
            });
            </script>');
        return $this;
    }

    /**
     * @return \Magento\Framework\Module\ModuleList|mixed
     * @throws \RuntimeException
     */
    public function getModuleList()
    {
        if ($this->modulesList === null) {
            $this->modulesList = self::om()->get(ModuleListInterface::class);
        }

        return $this->modulesList;
    }

    /**
     * Returns config value
     *
     * @param string $key The last part of XML_PATH_$area_CAPTCHA_ constant (case insensitive)
     * @param \Magento\Store\Model\Store $store
     * @return \Magento\Framework\App\Config\Element
     */
    public function getConfig($key, $store = null)
    {
        return $this->scopeConfig->getValue($key, ScopeInterface::SCOPE_STORE, $store);
    }

    public function formatUrlKey($str)
    {
        $urlKey = preg_replace('#[^0-9a-z]+#i', '-', $this->_productUrl()->formatUrlKey($str));
        $urlKey = strtolower($urlKey);
        $urlKey = trim($urlKey, '-');

        return $urlKey;
    }

    /**
     * @return \Magento\Catalog\Model\Product\Url
     */
    protected function _productUrl()
    {
        return $this->om()->get('Magento\Catalog\Model\Product\Url');
    }

    public function isModuleActive($code)
    {
        if (!isset($this->_moduleActive[$code])) {
            $this->_moduleActive[$code] = $this->_moduleManager->isEnabled($code);
        }
        return $this->_moduleActive[$code];
    }

    /**
     * ObjectManager instance
     *
     * @return ObjectManager
     * @throws \RuntimeException
     */
    public static function om()
    {
        return ObjectManager::getInstance();
    }

    /**
     * Get Magento version
     *
     * @return string
     */
    public static function getVersion()
    {
        /** @var ProductMetadataInterface $metaData */
        $metaData = self::_getMetaData();
        return $metaData->getVersion();
    }

    private function isEnterpriseEdition()
    {
        return in_array(strtolower(self::_getMetaData()->getEdition()), ['enterprise', 'b2b']);
    }

    public function compareMageVer($ceVer, $eeVer = null, $op = '>=')
    {
        return $this->isEnterpriseEdition()
            ? version_compare(self::getVersion(), null !== $eeVer ? $eeVer : $ceVer, $op)
            : version_compare(self::getVersion(), $ceVer, $op);
    }

    /**
     * Check whether sql date is empty
     *
     * @param string $date
     * @return boolean
     */
    static public function is_empty_date($date)
    {
        return preg_replace('#[ 0:-]#', '', $date) === '';
    }

    public function hasMageFeature($feature)
    {
        if (!isset($this->_hasMageFeature[$feature])) {
            $flag = false;
            switch ($feature) {
                case RfProduct::ROW_ID:
                    $flag = $this->isEnterpriseEdition() && $this->compareMageVer('2.1.0');
                    break;
                case RfProduct::BUNDLE_SEQ:
                    $flag = $this->isEnterpriseEdition() && $this->compareMageVer('2.2.0');
                    break;
                case RfProduct::SUPER_ATTR_ROW_ID:
                    $flag21 = $this->compareMageVer('2.1.10')
                        && $this->compareMageVer('2.2.0', '<');
                    $flag22 = $this->compareMageVer('2.2.5');
                    $flag = $this->isEnterpriseEdition() && ($flag21 || $flag22);
                    break;
                case RfProduct::BUNDLE_PARENT:
                    $flag = $this->compareMageVer('2.2.0');
                    break;
            }
            $this->_hasMageFeature[$feature] = $flag;
        }
        return $this->_hasMageFeature[$feature];
    }

    protected $_isoToPhpFormatConvertRegex;
    protected $_isoToPhpFormatConvert;
    protected $_phpToIsoFormatConvert = [
        'd' => 'dd', 'D' => 'EE', 'j' => 'd', 'l' => 'EEEE', 'N' => 'e', 'S' => 'SS',
        'w' => 'eee', 'z' => 'D', 'W' => 'ww', 'F' => 'MMMM', 'm' => 'MM', 'M' => 'MMM',
        'n' => 'M', 't' => 'ddd', 'L' => 'l', 'o' => 'YYYY', 'Y' => 'yyyy', 'y' => 'yy',
        'a' => 'a', 'A' => 'a', 'B' => 'B', 'g' => 'h', 'G' => 'H', 'h' => 'hh',
        'H' => 'HH', 'i' => 'mm', 's' => 'ss', 'e' => 'zzzz', 'I' => 'I', 'O' => 'Z',
        'P' => 'ZZZZ', 'T' => 'z', 'Z' => 'X', 'c' => 'yyyy-MM-ddTHH:mm:ssZZZZ',
        'r' => 'r', 'U' => 'U'
    ];

    public function convertIsoToPhpDateFormat($isoFormat)
    {
        if (null === $this->_isoToPhpFormatConvertRegex) {
            uasort($this->_phpToIsoFormatConvert, array($this, 'sortByLengthDescCallback'));
            $this->_isoToPhpFormatConvertRegex = sprintf('/%s/', implode('|',
                                                                         array_map('preg_quote',
                                                                                   $this->_phpToIsoFormatConvert)
            ));
        }
        return preg_replace_callback(
            $this->_isoToPhpFormatConvertRegex,
            array($this, 'regexIsoToPhpDateFormatCallback'),
            $isoFormat
        );
    }

    public function sortByLengthDescCallback($a, $b)
    {
        $a = strlen($a);
        $b = strlen($b);
        if ($a == $b) {
            return 0;
        }
        return ($a < $b) ? 1 : -1;
    }

    public function regexIsoToPhpDateFormatCallback($matches)
    {
        if (null === $this->_isoToPhpFormatConvert) {
            $this->_isoToPhpFormatConvert = array_flip($this->_phpToIsoFormatConvert);
        }
        return isset($this->_isoToPhpFormatConvert[$matches[0]]) ? $this->_isoToPhpFormatConvert[$matches[0]] : $matches[0];
    }

    public function hasEeGwsFilter()
    {
        if (null === $this->_ee_gws_filter) {
            $this->_ee_gws_filter = $this->isModuleActive('Magento_AdminGws')
                && $this->_backendAuth()->isLoggedIn()
                && !self::om()->get('Magento\AdminGws\Model\Role')->getIsAll();
//            $this->_ee_gws_filter = false;
        }
        return $this->_ee_gws_filter;
    }

    public function filterEeGwsStoreIds($sIds)
    {
        if ($this->hasEeGwsFilter()) {
            return array_intersect($sIds,
                                   self::om()->get('Magento\AdminGws\Model\Role')->getStoreIds());
        }
        return $sIds;
    }

    public function filterEeGwsWebsiteIds($wIds)
    {
        if ($this->hasEeGwsFilter()) {
            return array_intersect($wIds,
                                   self::om()->get('Magento\AdminGws\Model\Role')->getWebsiteIds());
        }
        return $wIds;
    }

    public function getEeGwsWebsiteIds()
    {
        if ($this->hasEeGwsFilter()) {
            return self::om()->get('Magento\AdminGws\Model\Role')->getWebsiteIds();
        }
        return array_keys($this->_storeManager->getWebsites(true));
    }

    public function getEeGwsStoreIds()
    {
        if ($this->hasEeGwsFilter()) {
            return self::om()->get('Magento\AdminGws\Model\Role')->getStoreIds();
        }
        return array_keys($this->_storeManager->getStores(true));
    }

    public static function logger( $file = null)
    {

        if (self::$_customLog === null) {
            $handler = self::getLoggerHandler();
            if ($file !== null) {
                $handler->setFile($file);
            }
            /** @var Monolog $logger */
            $logger = self::om()->get('Psr\Log\LoggerInterface');
            $logger->pushHandler($handler);
            self::$_customLog = $logger;
        }

        return self::$_customLog;
    }

    public function addCategoryIdForRewriteUpdate($categoryId, $seqId=null)
    {
        $_seqId = $categoryId;
        if ($seqId!==null) {
            $_seqId = $seqId;
        }
        $this->_categoryIdsToUpdate[] = $categoryId;
        $this->_categorySeqIdsToUpdate[] = $_seqId;
    }

    /**
     * @var \Unirgy\RapidFlow\Model\Profile
     */
    public $currentProfile;
    /**
     * @param int|null $store_id
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function updateCategoriesUrlRewrites($store_id = null)
    {
        $this->clearUrlPaths($this->_categoryIdsToUpdate);
        foreach (array_unique(array_filter($this->_categorySeqIdsToUpdate)) as $cId) {
            try {
                $this->updateCategoryUrlRewritesById($cId, $store_id);
            } catch (\Exception $e) {
                if ($__cp = $this->currentProfile) $__cp->getLogger()->error($e->getMessage());
            }
        }
        $this->restoreUrlPath($this->_categoryIdsToUpdate);
    }

    /**
     * @param $categoryId
     * @param int|null $store_id
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function updateCategoryUrlRewritesById($categoryId, $store_id)
    {
        $category = $this->categoryFactory->create()->load($categoryId);
        $category->setStoreId($store_id);
        $urlRewrites = array_merge(
            $this->categoryUrlRewriteGenerator()->generate($category, 1),
            $this->urlRewriteHandler()->generateProductUrlRewrites($category)
        );
        $this->urlPersist()->replace($urlRewrites);
    }

    /**
     * @return \Magento\CatalogUrlRewrite\Observer\UrlRewriteHandler
     */
    protected function urlRewriteHandler()
    {
        return $this->om()->get('Magento\CatalogUrlRewrite\Observer\UrlRewriteHandler');
    }

    /**
     * @return \Magento\UrlRewrite\Model\UrlPersistInterface
     */
    protected function urlPersist()
    {
        return $this->om()->get('Unirgy\RapidFlow\Model\UrlRewriteDbStorage');
    }

    /**
     * @return \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator
     */
    protected function categoryUrlRewriteGenerator()
    {
        return $this->om()->get('Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator');
    }


    /**
     * @return Logger
     */
    public static function getLoggerHandler()
    {
        return new Logger(new File());
    }

    public static function now($dayOnly = false)
    {
        return date($dayOnly ? 'Y-m-d' : 'Y-m-d H:i:s');
    }

    protected $_urlPathTmpTable = 'tmp_category_url_path';

    protected function clearUrlPaths($categoryIds)
    {
        if (count($categoryIds) === 0) {
            return;
        }
        $idField = 'entity_id';
        if ($this->hasMageFeature(AbstractResource::ROW_ID)) {
            $idField = 'row_id';
        }

        $res = $this->categoryFactory->create()->getResource();
        $table = AbstractResource::TABLE_CATALOG_CATEGORY_ENTITY . '_varchar';
        $table = $res->getTable($table);
        $eavAttrTable = $res->getTable(AbstractResource::TABLE_EAV_ATTRIBUTE);
        $con = $res->getConnection();
        $select = $con->select()
            ->from($table)
            ->where('attribute_id=?',
                new \Zend_Db_Expr('(SELECT attribute_id FROM ' . $eavAttrTable . ' WHERE attribute_code=\'url_path\' AND entity_type_id=3)'))
            ->where($idField . ' IN (?)', $categoryIds);

        $con->createTemporaryTableLike($this->_urlPathTmpTable, $table, true);

        $tmpQ = $con->insertFromSelect($select, $this->_urlPathTmpTable, [], AdapterInterface::INSERT_IGNORE);

        $con->query($tmpQ);

        //$query = $con->deleteFromSelect($select, $table);

        $query = sprintf("DELETE ccv FROM %s as ccv INNER JOIN %s ea ON ccv.attribute_id=ea.attribute_id and ea.attribute_code='url_path' and ea.entity_type_id=3 WHERE ccv.$idField in (%s)",
            $table, $eavAttrTable, $con->quote($categoryIds)
        );

        return $con->query($query);
    }
    protected function restoreUrlPath($categoryIds)
    {
        if (count($categoryIds) === 0) {
            return;
        }
        $idField = 'entity_id';
        if ($this->hasMageFeature(AbstractResource::ROW_ID)) {
            $idField = 'row_id';
        }
        $res = $this->categoryFactory->create()->getResource();
        $table = AbstractResource::TABLE_CATALOG_CATEGORY_ENTITY . '_varchar';
        $table = $res->getTable($table);
        $con = $res->getConnection();
        $select = $con->select()
            ->from($this->_urlPathTmpTable)
            ->where($idField . ' IN (?)', $categoryIds);

        $tmpQ = $con->insertFromSelect($select, $table, [], AdapterInterface::INSERT_IGNORE);
        $con->query($tmpQ);
        $con->delete($this->_urlPathTmpTable, [$idField . ' IN (?)' => $categoryIds]);
    }
}
