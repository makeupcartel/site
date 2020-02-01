<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Scope config Provider model
 */
class ConfigProvider
{
    /**
     * xpath prefix of module
     */
    const PATH_PREFIX = 'amlocator';

    /**#@+
     * Constants defined for xpath of system configuration
     */
    const META_TITLE = 'general/meta_title';

    const META_DESCRIPTION = 'general/meta_description';
    /**#@-*/
    const XPATH_MAP_WIDTH = 'style_settings/map_width';
    const XPATH_MAP_HEIGHT = 'style_settings/map_height';
    const XPATH_STORE_LIST_WIDTH = 'style_settings/store_list_width';
    const XPATH_STORE_LIST_HEIGHT = 'style_settings/store_list_height';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ConfigProvider constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * An alias for scope config with default scope type SCOPE_STORE
     *
     * @param string $key
     * @param string|null $scopeCode
     * @param string $scopeType
     *
     * @return string|null
     */
    public function getValue($key, $scopeCode = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue(self::PATH_PREFIX . '/' . $key, $scopeType, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getMetaTitle($scopeCode = null)
    {
        return $this->getValue(self::META_TITLE, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getMetaDescription($scopeCode = null)
    {
        return $this->getValue(self::META_DESCRIPTION, $scopeCode);
    }

    /**
     * @return string
     */
    public function getMapWidth()
    {
        return $this->getValue(self::XPATH_MAP_WIDTH);
    }

    /**
     * @return string
     */
    public function getMapHeight()
    {
        return $this->getValue(self::XPATH_MAP_HEIGHT);
    }

    /**
     * @return string
     */
    public function getStoreListWidth()
    {
        return $this->getValue(self::XPATH_STORE_LIST_WIDTH);
    }

    /**
     * @return string
     */
    public function getStoreListHeight()
    {
        return $this->getValue(self::XPATH_MAP_HEIGHT);
    }
}
