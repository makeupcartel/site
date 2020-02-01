<?php

namespace Unirgy\SimpleLicense\Helper;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ObjectManager;
use Psr\Log\LoggerInterface;
use Unirgy\SimpleLicense\Model\License;

class Data extends AbstractHelper
{

    /**
     * @var LoggerInterface
     */
    private static $logger;

    /**
     * @var CacheInterface
     */
    private static $cache;

    /**
     * @var array
     */
    protected static $modulesList;

    /** @var ScopeConfigInterface $config */
    static private $config;

    /**
     * @return ObjectManager
     * @throws \RuntimeException
     */
    protected static function om()
    {
        return ObjectManager::getInstance();
    }

    /**
     * @return ScopeConfigInterface|mixed
     * @throws \RuntimeException
     */
    public static function config()
    {
        if (self::$config === null) {
            self::$config = self::om()->get('Magento\Framework\App\Config\ScopeConfigInterface');
        }
        return self::$config;
    }

    /**
     * @return \Magento\Framework\Module\ModuleList|mixed
     * @throws \RuntimeException
     */
    public static function getModuleList()
    {
        if (self::$modulesList === null) {
            self::$modulesList = self::om()->get('Magento\Framework\Module\ModuleListInterface');
        }

        return self::$modulesList;
    }

    /**
     * @return mixed|LoggerInterface
     * @throws \RuntimeException
     */
    public static function logger()
    {
        if (self::$logger === null) {
            self::$logger = self::om()->get('\Psr\Log\LoggerInterface');
        }
        return self::$logger;
    }

    /**
     * @return CacheInterface
     * @throws \RuntimeException
     */
    public static function cache()
    {
        if (self::$cache === null) {
            self::$cache = self::om()->get('\Magento\Framework\App\CacheInterface');
        }
        return self::$cache;
    }

    /**
     * @return \Unirgy\SimpleLicense\Model\ResourceModel\License\Collection
     * @throws \RuntimeException
     */
    public static function getLicenseCollection()
    {
        return self::om()->get('Unirgy\SimpleLicense\Model\License')->getCollection();
    }

    /**
     * @param $key
     * @return License
     * @throws \RuntimeException
     */
    public static function getLicenseModel($key)
    {
        if($key instanceof License){
            return $key;
        }
        return self::om()->create('Unirgy\SimpleLicense\Model\License')->load($key, 'license_key');
    }
    public function checkUpdates()
    {

    }
}
