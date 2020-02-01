<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Plugin\Search\Helper;

use Magento\Search\Helper\Data as NativeData;

class Data
{
    /**
     * @var \Amasty\Xsearch\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Xsearch\Helper\Data $helper
    ) {
        $this->helper = $helper;
        $this->storeManager = $storeManager;
    }

    /**
     * @param NativeData $subject
     * @param \Closure $proceed
     * @param string $query
     * @return string
     */
    public function aroundGetResultUrl(
        NativeData $subject,
        \Closure $proceed,
        $query = null
    ) {
        $seoKey = $this->helper->getSeoKey();
        if ($this->helper->isSeoUrlsEnabled() && $seoKey && $query) {
            $url = rtrim($this->storeManager->getStore()->getBaseUrl(), '/') . '/' . $seoKey . '/' . $query;
        } else {
            $url = $proceed($query);
        }

        return $url;
    }
}
