<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */

namespace Amasty\Promo\Model\Rule\Action\Discount;

/**
 * Action name: Auto add the same product
 */
class SameProduct extends AbstractDiscount
{
    /**
     * @inheritdoc
     */
    protected function _addFreeItems(
        \Magento\SalesRule\Model\Rule $rule,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        $qty
    ) {
        if (!$this->isItemValid($item)) {
            return;
        }

        $qty = $this->_getFreeItemsQty($rule, $item);

        if ($qty < 1 || $this->_skip($rule, $item)) {
            return;
        }

        $ampromoRule = $this->ruleResolver->getFreeGiftRule($rule);

        $discountData = [
            'discount_item' => $ampromoRule->getItemsDiscount(),
            'minimal_price' => $ampromoRule->getMinimalItemsPrice(),
        ];
        if ($item->getProductType() === \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $sku = $item->getSku();
        } else {
            $sku = $item->getProduct()->getData('sku');
        }

        $this->promoRegistry->addPromoItem(
            $sku,
            $qty,
            $rule->getId(),
            $discountData,
            $ampromoRule->getType(),
            $rule->getDiscountAmount()
        );
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem|\Magento\Quote\Model\Quote\Item $item
     *
     * @return bool
     */
    protected function isItemValid($item)
    {
        return !$this->promoItemHelper->isPromoItem($item)
            && !$item->getParentItemId()
            && $item->getProductType() !== 'downloadable';
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem|\Magento\Quote\Model\Quote\Item $item
     * @return int|float
     */
    protected function _getFreeItemsQty(
        \Magento\SalesRule\Model\Rule $rule,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item
    ) {
        $discountStep   = max(1, $rule->getDiscountStep());
        $discountAmount = max(1, $rule->getDiscountAmount());
        $maxDiscountQty = 100000;
        if ($rule->getDiscountQty()) {
            $maxDiscountQty = (int) max(1, $rule->getDiscountQty());
        }

        return min(
            floor($item->getQty() / $discountStep) * $discountAmount,
            $maxDiscountQty
        );
    }
}
