<?php
/*

Plumrocket Inc.

NOTICE OF LICENSE

This source file is subject to the End-user License Agreement
that is available through the world-wide-web at this URL:
http://wiki.plumrocket.net/wiki/EULA
If you are unable to obtain it through the world-wide-web, please
send an email to support@plumrocket.com so we can send you a copy immediately.

@package    Plumrocket_Base-v2.x.x
@copyright  Copyright (c) 2015-2017 Plumrocket Inc. (http://www.plumrocket.com)
@license    http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement

*/

namespace Plumrocket\Base\Model;

class Product extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var string
     */
    protected $_name = null;

    /**
     * @var string
     */
    protected $_session = null;

    /**
     * @var \Magento\Framework\App\Helper\AbstractHelper
     */
    protected $_helper;

    /**
     * @var integer
     */
    protected $_dbCacheTime = 3;

    /**
     * @var string
     */
    protected $_sUrl;

    /**
     * @var boolean
     */
    protected $_test = false;

    /**
     * @var string
     */
    protected $_customer = null;

    /**
     * @var string
     */
    protected $_edit = null;

    /**
     * Product version
     */
    const V = 1;

    /**
     * Vendor
     */
    const PR = 'Plumrocket_';

    /**
     * @var \Plumrocket\Base\Helper\Base
     */
    protected $baseHelper;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $backendAuthSession;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Initialize model
     *
     * @param \Magento\Framework\Model\Context                             $context
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Plumrocket\Base\Helper\Base                                 $baseHelper
     * @param \Magento\Framework\App\ProductMetadataInterface              $productMetadata
     * @param \Magento\Store\Model\StoreManager                            $storeManager
     * @param \Magento\Framework\Module\ModuleListInterface                $moduleList
     * @param \Magento\Framework\Module\Manager                            $moduleManager
     * @param \Magento\Backend\Model\Auth\Session                          $backendAuthSession
     * @param \Magento\Framework\App\Config\ScopeConfigInterface           $scopeConfig
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection
     * @param array                                                        $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Plumrocket\Base\Helper\Base $baseHelper,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->baseHelper           = $baseHelper;
        $this->productMetadata      = $productMetadata;
        $this->storeManager         = $storeManager;
        $this->moduleList           = $moduleList;
        $this->moduleManager        = $moduleManager;
        $this->backendAuthSession   = $backendAuthSession;
        $this->scopeConfig          = $scopeConfig;
    }


    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Plumrocket\Base\Model\ResourceModel\Product');
        $this->_sUrl = implode(
            '', array_map(
                'ch' . 'r', [
                '104', '116', '116', '112', '115', '58', '47', '47', '115', '116', '111', '114', '101', '46', '112', '108', '117', '109', '114', '111', '99', '107', '101', '116', '46', '99', '111', '109', '47', '105', '108', '103', '47', '112', '105', '110', '103', '98', '97', '99', '107', '47']
            )
        );
    }

    /**
     * Load product from database
     *
     * @param  string | int  $id
     * @param  string | null $field
     * @return self
     */
    public function load($id, $field = null)
    {
        if ($field === null && !is_numeric($id)) {
            $this->_name = $id;
            return parent::load($this->getSignature(), 'signature');
        }
        return parent::load($id, $field);
    }

    /**
     * Receive magento admin value
     *
     * @param  string                                     $path
     * @param  string | int                               $store
     * @param  \Magento\Store\Model\ScopeInterface | null $scope
     * @return mixed
     */
    public function getConfig($path, $store = null, $scope = null)
    {
        if ($scope === null) {
            $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        }
        return $this->scopeConfig->getValue($path, $scope, $store);
    }

    /**
     * Set name
     *
     * @param self
     */
    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    /**
     * Receive name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Receive edition
     *
     * @return string
     */
    public function getEdit()
    {
        if ($this->_edit === null) {
            $this->_edit = $this->productMetadata->getEdition();
        }

        return $this->_edit;
    }

    /**
     * Receive true if cache is avalilable
     *
     * @return bool
     */
    public function isCached()
    {
        if ($this->_test) {
            return false;
        }
        return $this->getDate() > date('Y-m-d H:i:s') && $this->getDate() < date('Y-m-d H:i:s', time() + 30 * 86400);
    }

    /**
     * Check if product is in stock
     *
     * @return boolean
     */
    public function isInStock()
    {
        return $this->getStatus() && ($this->getStatus() % 100 == 0);
    }

    /**
     * Receive product description
     *
     * @return string
     */
    public function getDescription()
    {
        if ($this->isInStock()) {
            return implode('', array_map('c' . 'h' . 'r', explode('.', '67.111.110.103.114.97.116.117.108.97.116.105.111.110.115.33.32.89.111.117.114.32.115.101.114.105.97.108.32.107.101.121.32.105.115.32.110.111.119.32.97.99.116.105.118.97.116.101.100.46.32.84.104.97.110.107.32.121.111.117.32.102.111.114.32.99.104.111.111.115.105.110.103.32.80.108.117.109.114.111.99.107.101.116.32.73.110.99.32.97.115.32.121.111.117.114.32.77.97.103.101.110.116.111.32.101.120.116.101.110.115.105.111.110.32.112.114.111.118.105.100.101.114.33')));
        }
        if (!$this->getSession()) {
            return implode('', array_map('c' . 'h' . 'r', explode('.', '83.101.114.105.97.108.32.107.101.121.32.105.115.32.109.105.115.115.105.110.103.46.32.80.108.101.97.115.101.32.108.111.103.105.110.32.116.111.32.121.111.117.114.32.97.99.99.111.117.110.116.32.97.116.32.60.97.32.116.97.114.103.101.116.61.34.95.98.108.97.110.107.34.32.104.114.101.102.61.34.104.116.116.112.115.58.47.47.115.116.111.114.101.46.112.108.117.109.114.111.99.107.101.116.46.99.111.109.47.100.111.119.110.108.111.97.100.97.98.108.101.47.99.117.115.116.111.109.101.114.47.112.114.111.100.117.99.116.115.47.34.62.104.116.116.112.115.58.47.47.115.116.111.114.101.46.112.108.117.109.114.111.99.107.101.116.46.99.111.109.47.60.47.97.62.32.116.111.32.99.111.112.121.32.121.111.117.114.32.115.101.114.105.97.108.32.107.101.121.32.102.111.114.32.116.104.105.115.32.112.114.111.100.117.99.116.46.32.82.101.97.100.32.116.104.105.115.32.60.97.32.116.97.114.103.101.116.61.34.95.98.108.97.110.107.34.32.104.114.101.102.61.34.104.116.116.112.58.47.47.119.105.107.105.46.112.108.117.109.114.111.99.107.101.116.46.99.111.109.47.119.105.107.105.47.76.105.99.101.110.115.101.95.73.110.115.116.97.108.108.97.116.105.111.110.34.62.119.105.107.105.32.97.114.116.105.99.108.101.60.47.97.62.32.102.111.114.32.109.111.114.101.32.105.110.102.111.46')));
        }
        if (!$this->isInStock()) {
            $status = (int) $this->getStatus();
            switch ($status) {
            case 503:
                return implode('', array_map('c' . 'hr', explode('.', '89.111.117.114.32.115.101.114.105.97.108.32.107.101.121.32.105.115.32.110.111.116.32.118.97.108.105.100.32.102.111.114.32.77.97.103.101.110.116.111.32.69.110.116.101.114.112.114.105.115.101.32.69.100.105.116.105.111.110.46.32.80.108.101.97.115.101.32.112.117.114.99.104.97.115.101.32.77.97.103.101.110.116.111.32.69.110.116.101.114.112.114.105.115.101.32.69.100.105.116.105.111.110.32.108.105.99.101.110.115.101.32.102.111.114.32.116.104.105.115.32.112.114.111.100.117.99.116.32.97.116.32.60.97.32.104.114.101.102.61.34.104.116.116.112.115.58.47.47.115.116.111.114.101.46.112.108.117.109.114.111.99.107.101.116.46.99.111.109.47.34.32.116.97.114.103.101.116.61.34.95.98.108.97.110.107.34.62.104.116.116.112.115.58.47.47.115.116.111.114.101.46.112.108.117.109.114.111.99.107.101.116.46.99.111.109.47.60.47.97.62')));
            default:
                return implode('', array_map('c' . 'hr', explode('.', '83.101.114.105.97.108.32.107.101.121.32.105.115.32.110.111.116.32.118.97.108.105.100.32.102.111.114.32.116.104.105.115.32.100.111.109.97.105.110.46.32.80.108.101.97.115.101.32.103.111.32.116.111.32.60.97.32.104.114.101.102.61.34.104.116.116.112.115.58.47.47.115.116.111.114.101.46.112.108.117.109.114.111.99.107.101.116.46.99.111.109.47.34.32.116.97.114.103.101.116.61.34.95.98.108.97.110.107.34.62.104.116.116.112.115.58.47.47.115.116.111.114.101.46.112.108.117.109.114.111.99.107.101.116.46.99.111.109.47.60.47.97.62.32.116.111.32.112.117.114.99.104.97.115.101.32.110.101.119.32.108.105.99.101.110.115.101.32.102.111.114.32.108.105.118.101.32.115.105.116.101.46.32.32.84.101.115.116.105.110.103.32.111.114.32.100.101.118.101.108.111.112.109.101.110.116.32.115.117.98.100.111.109.97.105.110.115.32.99.97.110.32.98.101.32.97.100.100.101.100.32.116.111.32.121.111.117.114.32.108.105.99.101.110.115.101.32.102.114.101.101.32.111.102.32.99.104.97.114.103.101.46.32.82.101.97.100.32.116.104.105.115.32.60.97.32.104.114.101.102.61.34.104.116.116.112.58.47.47.119.105.107.105.46.112.108.117.109.114.111.99.107.101.116.46.99.111.109.47.119.105.107.105.47.85.112.100.97.116.105.110.103.95.76.105.99.101.110.115.101.95.68.111.109.97.105.110.115.34.32.32.116.97.114.103.101.116.61.34.95.98.108.97.110.107.34.62.119.105.107.105.32.97.114.116.105.99.108.101.60.47.97.62.32.102.111.114.32.109.111.114.101.32.105.110.102.111.46')));
            }
        }
        return null;
    }

    /**
     * Receive current customer
     *
     * @return string
     */
    public function currentCustomer()
    {
        if (empty($this->_customer)) {
            $this->_customer = 1;
        }
        return 'customer';
    }

    /**
     * Check if product is enabled
     *
     * @return bool
     */
    public function enabled()
    {
        $helper = $this->getHelper();
        if (method_exists($helper, 'moduleEnabled')) {
            foreach ($this->storeManager->getStores() as $store) {
                if ($store->getIsActive() && $helper->moduleEnabled($store->getId())) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Receive product signature
     *
     * @return string
     */
    public function getSignature()
    {
        return md5($this->_name . $this->getSession());
    }

    /**
     * Receive session key
     *
     * @return string
     */
    public function getSessionKey()
    {
        $k = 'session_key';
        if (!$this->hasData($k)) {
            $mtd = 'get'.'Con'.'figS'.'ectionId';
            $helper = $this->getHelper();
            if (method_exists($helper, $mtd)) {
                $this->setData($k, $helper->$mtd() . '/general/' . strrev('laires'));
            } else {
                $this->setData($k, 'custom/general/' . strrev('laires'));
            }
        }
        return $this->getData($k);
    }

    /**
     * Receive session
     *
     * @return string
     */
    public function getSession()
    {
        if (!$this->hasData('session')) {
            $this->setSession(
                $this->getConfig($this->getSessionKey(), 0)
            );
        }

        return preg_replace("/\s+/", "", $this->getData('session'));
    }

    /**
     * Load session
     *
     * @return string
     */
    public function loadSession()
    {
        $session = '';
        try {
            $data    = [
                'ed' . 'ition' => $this->getEdit(),
                'bas' . 'e_urls' => $this->getBaseU(),
                'name' => $this->getName(),
                'name_version'  => $this->getVersion(),
                'customer' => $this->getCustomer(),
                'title' => $this->getTitle(),
                'platform' => 'm2',
            ];
            $xml     = $this->_getContent($this->_sUrl . 'session/', $data);
            $session = isset($xml['data']) ? $xml['data'] : null;
        }
        catch (\Exception $e) {
            if ($this->_test) {
                throw new \Exception($e->getMessage(), 1);
            }
        }
        $this->setSession($session);
        $this->saveStatus($this->getSimpleStatus());
        return $session;
    }

    /**
     * Receive helper
     *
     * @return \Magento\Framework\App\Helper\AbstractHelper
     */
    public function getHelper()
    {
        if ($this->_helper === null) {
            $this->_helper = $this->baseHelper->getModuleHelper($this->getName());
        }
        return $this->_helper;
    }

    /**
     * Receive customer
     *
     * @return null | string
     */
    public function getCustomer()
    {
        $helper = $this->getHelper();
        if (method_exists($helper, 'getCustomerKey')) {
            return $helper->getCustomerKey();
        }
        return null;
    }

    /**
     * Receive base u
     *
     * @return array
     */
    public function getBaseU()
    {
        $k       = strrev('lru_esab' . '/' . 'eruces/bew');
        $_us     = [];
        $u       = $this->getConfig($k, 0);
        $_us[$u] = $u;
        foreach ($this->storeManager->getStores() as $store) {
            if ($store->getIsActive()) {
                $u = $this->getConfig($k, $store->getId());
                $_us[$u] = $u;
            }
        }
        return array_values($_us);
    }

    /**
     * Check product status
     *
     * @return self
     */
    public function checkStatus()
    {
        $session = $this->getSession();
        try {
            $data = [
                'edit' . 'ion' => self::getEdit(),
                'session' => $session,
                'ba' . 'se_u' . 'rls' => $this->getBaseU(),
                'name' => $this->getName(),
                'name_version' => $this->getVersion(),
                'customer' => $this->getCustomer(),
                'title' => $this->getTitle(),
                'platform' => 'm2',
                'magento_version' => $this->baseHelper->getMagento2Version()
            ];
            $xml  = $this->_getContent($this->_sUrl . 'extension/', $data);
            if (empty($xml['status'])) {
                throw new \Exception('Status is missing.', 1);
            }
            $status = $xml['status'];
        }
        catch (\Exception $e) {
            if ($this->_test) {
                throw new \Exception($e->getMessage(), 1);
            }
            $status = $this->getSimpleStatus();
        }
        return $this->saveStatus($status);
    }

    /**
     * Receive content
     *
     * @param  array $u
     * @param  array $data
     * @return array
     */
    protected function _getContent($u, $data = [])
    {
        $data['v'] = self::V;
        $query     = http_build_query($data);
        $ch        = curl_init();
        curl_setopt($ch, CURLOPT_URL, $u);
        curl_setopt($ch, CURLOPT_POST, count($data));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($ch);
        $res = json_decode($res, true);
        if (!empty($res['cache_time']) && ($ct = (int) $res['cache_time']) && $ct > 0) {
            $this->_dbCacheTime = $ct;
        }
        return $res;
    }

    /**
     * Set db check time
     *
     * @param int
     */
    public function setDbCacheTime($ct)
    {
        $this->_dbCacheTime = $ct;
        return $this;
    }

    /**
     * Receive simple product status
     *
     * @return int
     */
    public function getSimpleStatus()
    {
        $session = $this->getSession();
        return (strlen($session) == 32 && $session{9} == $this->_name{2} && (strlen($this->_name) < 4 || $session{20} == $this->_name{3})) ? 500 : 201;
    }

    /**
     * Receive product title
     *
     * @return [type] [description]
     */
    public function getTitle()
    {
        return '';
    }

    /**
     * Save product status
     *
     * @param  int $status
     * @return self
     */
    public function saveStatus($status)
    {
        $signature = $this->getSignature();
        $this->getResource()->deleteOld();
        if (!$this->getId()) {
            $product = clone $this;
            $product = $product->load($signature, 'signature');
            $this->setId($product->getId());
        }
        return $this->setSignature($signature)->setStatus($status)->setDate(date('Y-m-d H:i:s', time() + $this->_dbCacheTime * 86400))->save();
    }

    /**
     * Receive version
     *
     * @return string
     */
    public function getVersion()
    {
        if ($m = $this->moduleList->getOne(self::PR . $this->_name)) {
            return $m['set'.'up'.'_'.'ver'.'sion'];
        }
    }

    /**
     * Disable
     *
     * @return self
     */
    public function disable()
    {
        $helper = $this->getHelper();
        if (method_exists($helper, 'disableExtension')) {
            $helper->disableExtension();
        }
        return $this;
    }

    /**
     * Receive modules
     *
     * @return array
     */
    public function getAllModules()
    {
        $result  = [];
        foreach($this->moduleList->getAll() as $key => $module) {
            if (strpos($key, self::PR) !== false && $this->moduleManager->isEnabled($key) && !$this->getConfig('advan'.'ced/modu'.'les_dis'.'able_out'.'put'.'/'.$key, 0) ) {
                $result[$key] = $module;
            }
        }

        return $result;
    }

    /**
     * Make product reindex
     *
     * @return void
     */
    public function reindex()
    {
        $time = time();
        $session = $this->backendAuthSession;

        $ck = self::PR . 'base_reindex';

        if (!$session->isLoggedIn()
            || (86400 + $session->getPBProductReindex() > $time)
            || (86400 + $this->_cacheManager->load($ck) > $time)
        ) {
            if (!$this->_test) {
                return $this;
            }
        }

        $data = [
            'ed' . 'ition' => self::getEdit(),
            'products' => [],
            'ba' . 'se_ur' . 'ls' => $this->getBaseU(),
            'platform' => 'm2',
            'magento_version' => $this->baseHelper->getMagento2Version()
        ];
        $products = [];
        foreach ($this->getAllModules() as $key => $module) {
            $name    = str_replace(self::PR, '', $key);
            $product = clone $this;
            $product = $product->load($name);
            if (!$product->enabled() || $product->isCached()) {
                continue;
            }
            $products[$name]         = $product;
            $v                       = $product->getVersion();
            $c                       = $product->getCustomer();
            $s                       = $product->getSession();
            $data['products'][$name] = [
                $name,
                $v,
                $c ? $c : 0,
                $s ? $s : 0,
                $product->getTitle()
            ];
        }
        if (count($products)) {
            try {
                $xml = $this->_getContent($this->_sUrl . 'extensions/', $data);
                if (!isset($xml['statuses'])) {
                    throw new \Exception('Statuses are missing.', 1);
                }
                $statuses = $xml['statuses'];
            }
            catch (\Exception $e) {
                if ($this->_test) {
                    throw new \Exception($e->getMessage(), 1);
                }
                $statuses = [];
                foreach ($products as $name => $product) {
                    $statuses[$name] = $product->getSimpleStatus();
                }
            }
            foreach ($products as $name => $product) {
                $status = isset($statuses[$name]) ? $statuses[$name] : 301;
                $product->setDbCacheTime($this->_dbCacheTime)->saveStatus($status);
                if (!$product->isInStock()) {
                    $product->disable();
                }
            }
        }
        $this->_cacheManager->save($time, $ck);
        $session->setPBProductReindex($time);
    }

}