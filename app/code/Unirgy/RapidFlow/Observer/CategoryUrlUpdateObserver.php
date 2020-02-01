<?php
/**
 * Created by pp
 * @project magento202
 */

namespace Unirgy\RapidFlow\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManager;

class CategoryUrlUpdateObserver implements ObserverInterface
{
    /**
     * @var \Unirgy\RapidFlow\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * CategoryUrlUpdateObserver constructor.
     * @param \Unirgy\RapidFlow\Helper\Data $helper
     */
    public function __construct(
        \Unirgy\RapidFlow\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->helper = $helper;
        $this->_storeManager = $storeManager;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Unirgy\RapidFlow\Model\Profile $profile */
        $profile = $observer->getData('profile');
        $storeId = $profile->getStoreId();
        $this->helper->currentProfile = $profile;
        $this->helper->updateCategoriesUrlRewrites($storeId);
        if ($storeId !== 0) {
            // update category url rewrites for default store too
            $this->helper->updateCategoriesUrlRewrites(0);
        } else {
            $store = $this->_storeManager->getDefaultStoreView();
            if ($store) $this->helper->updateCategoriesUrlRewrites($store->getId());
        }
        $this->helper->currentProfile = null;
    }
}
