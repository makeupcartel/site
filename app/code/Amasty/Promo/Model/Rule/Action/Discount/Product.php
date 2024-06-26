<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Model\Rule\Action\Discount;

/**
 * Action name: Auto add promo items with products
 */
class Product extends AbstractDiscount
{
    /**
     * {@inheritdoc}
     */
    protected function _getFreeItemsQty(
        \Magento\SalesRule\Model\Rule $rule,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item
    ) {
        return $this->getPromoQtyByStep($rule, $item);
    }
}
