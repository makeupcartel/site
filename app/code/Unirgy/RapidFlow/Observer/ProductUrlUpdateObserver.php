<?php
/**
 * Created by pp
 * @project magento202
 */

namespace Unirgy\RapidFlow\Observer;

use Magento\Framework\Event\ObserverInterface;

class ProductUrlUpdateObserver implements ObserverInterface
{
    /**
     * @var \Unirgy\RapidFlow\Helper\Url
     */
    protected $helper;

    /**
     * CategoryUrlUpdateObserver constructor.
     * @param \Unirgy\RapidFlow\Helper\Url $helper
     */
    public function __construct(\Unirgy\RapidFlow\Helper\Url $helper)
    {
        $this->helper = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Unirgy\RapidFlow\Model\Profile $profile */
        $profile = $observer->getData('profile');
        try {
            $this->helper->updateProductsUrlRewrites($profile->getStoreId());
        } catch (\Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException $e) {
            $profile->getLogger()->error($e->getLogMessage());
        }
    }
}
