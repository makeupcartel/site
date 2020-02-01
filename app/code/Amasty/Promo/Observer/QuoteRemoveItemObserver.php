<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Mark item as deleted to prevent it's auto-addition
 */
class QuoteRemoveItemObserver implements ObserverInterface
{
    const CHECKOUT_ROUTER = 'amasty_checkout';
    const CHECKOUT_DELETE = 'remove-item';

    /**
     * @var \Amasty\Promo\Helper\Item
     */
    protected $promoItemHelper;

    /**
     * @var \Amasty\Promo\Model\Registry
     */
    protected $promoRegistry;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Amasty\Promo\Model\ItemRegistry\PromoItemRegistry
     */
    private $promoItemRegistry;

    public function __construct(
        \Amasty\Promo\Helper\Item $promoItemHelper,
        \Amasty\Promo\Model\Registry $promoRegistry,
        \Magento\Framework\App\RequestInterface $request,
        \Amasty\Promo\Model\ItemRegistry\PromoItemRegistry $promoItemRegistry
    ) {
        $this->promoItemHelper = $promoItemHelper;
        $this->promoRegistry = $promoRegistry;
        $this->_request = $request;
        $this->promoItemRegistry = $promoItemRegistry;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote\Item $item */
        $item = $observer->getEvent()->getQuoteItem();

        // Additional request checks to mark only explicitly deleted items
        if (($this->_request->getActionName() == 'delete'
                && $this->_request->getParam('id') == $item->getId())
            || ($this->_request->getActionName() == 'removeItem'
                && $this->_request->getParam('item_id') == $item->getId())
            || $this->isDeleteFromCheckout()
        ) {
            if (!$item->getParentId()
                && $this->promoItemHelper->isPromoItem($item)
            ) {
                $this->promoRegistry->deleteProduct($item);
            }

            $this->deleteAddedProducts($item);
        }
    }

    /**
     * @return bool
     */
    private function isDeleteFromCheckout()
    {
        $queryString = $this->_request->getRequestString();

        return strpos($queryString, self::CHECKOUT_ROUTER) !== false
            && strpos($queryString, self::CHECKOUT_DELETE) !== false;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return bool
     */
    private function deleteAddedProducts(\Magento\Quote\Model\Quote\Item $item)
    {
        $deleteProductSku = $item->getProduct()->getData('sku');
        $deleteRuleId = $this->promoItemHelper->getRuleId($item);

        if (!$deleteRuleId) {
            return false;
        }

        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($item->getQuote()->getAllItems() as $item) {
            if ($deleteProductSku == $item->getSku() && $this->promoItemHelper->getRuleId($item) == $deleteRuleId) {
                $item->getQuote()->removeItem($item->getItemId());
            }
        }

        return false;
    }
}
