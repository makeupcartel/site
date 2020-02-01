<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Url;

use Magento\Store\Model\StoreManagerInterface;
use Aheadworks\Blog\Model\Source\Config\Seo\UrlType;
use Aheadworks\Blog\Model\Config;

class TypeResolver
{

    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Config $config,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    /**
     * Check if category included into URL
     *
     * @return bool
     */
    public function isCategoryIncl()
    {
        $urlType = $this->config->getSeoUrlType($this->getStoreId());
        return $urlType == UrlType::URL_INC_CATEGORY ? true : false;
    }

    /**
     * Check if category excluded from URL
     *
     * @return bool
     */
    public function isCategoryExcl()
    {
        $urlType = $this->config->getSeoUrlType($this->getStoreId());
        return $urlType == UrlType::URL_EXC_CATEGORY ? true : false;
    }

    /**
     * Get current store id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }
}
