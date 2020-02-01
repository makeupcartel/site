<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * event name: sales_order_place_after
 */
class OrderPlaceAfterObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getOrder();

        $prefix = $this->scopeConfig->getValue(
            'ampromo/messages/prefix',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($prefix) {
            foreach ($order->getAllItems() as $item) {
                $buyRequest = $item->getBuyRequest();

                if (isset($buyRequest['options']['ampromo_rule_id'])) {
                    $item->setName($prefix . ' ' . $item->getName());
                }
            }
        }
    }
}
