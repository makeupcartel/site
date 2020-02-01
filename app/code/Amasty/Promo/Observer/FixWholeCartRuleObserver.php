<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * sales_quote_collect_totals_before
 *
 * Fix issue with whole cart rules.
 * Customer shouldn't have only promo items in cart
 */
class FixWholeCartRuleObserver implements ObserverInterface
{
    /**
     * @var \Amasty\Promo\Helper\Item
     */
    private $promoItemHelper;

    /**
     * @var \Amasty\Promo\Model\Registry
     */
    private $promoItemRegistry;

    public function __construct(
        \Amasty\Promo\Helper\Item $promoItemHelper,
        \Amasty\Promo\Model\ItemRegistry\PromoItemRegistry $promoItemRegistry
    ) {
        $this->promoItemHelper = $promoItemHelper;
        $this->promoItemRegistry = $promoItemRegistry;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->promoItemRegistry->resetQtyAllowed();
        $hasNonfreeItems = false;
        foreach ($observer->getQuote()->getAllItems() as $item) {
            if (!$this->promoItemHelper->isPromoItem($item)) {
                $hasNonfreeItems = true;
                break;
            }
        }

        if (!$hasNonfreeItems) {
            $observer->getQuote()->removeAllItems();
        }
    }
}
