<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */
namespace Amasty\Rules\Model\Rule\Action\Discount;

class EachnFixprice extends Eachn
{

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data Data
     */
    protected function _calculate($rule, $item)
    {
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $discountData = $this->discountFactory->create();
        $allItems = $this->getSortedItems($item->getAddress(), $rule, $this->getSortOrder($rule,self::DEFAULT_SORT_ORDER));
        if ($rule->getAmrulesRule()->getUseFor() == self::USE_FOR_SAME_PRODUCT) {
            $allItems = $this->reduceItems($allItems, $rule);
        }
        $allItems = $this->skipEachN($allItems, $rule);
        $itemsId = $this->getItemsId($allItems);

        /** @var \Magento\Quote\Model\Quote\Item\AbstractItem $allItem */
        foreach ($allItems as $i => $allItem) {
            if (in_array($item->getAmrulesId(), $itemsId) && $allItem->getAmrulesId()===$item->getAmrulesId()) {
                $itemQty = $this->getArrayValueCount($itemsId, $item->getAmrulesId());
                $itemPrice = $this->rulesProductHelper->getItemPrice($item);
                $baseItemPrice = $this->rulesProductHelper->getItemBasePrice($item);
                $baseAmount = $baseItemPrice - $rule->getDiscountAmount();
                $quoteAmount = $this->priceCurrency->convert($rule->getDiscountAmount(), $item->getQuote()->getStore());
                $quoteAmount = $itemPrice - $quoteAmount;
                $itemBaseOriginalPrice = $this->rulesProductHelper->getItemBaseOriginalPrice($item);
                $baseOriginalAmount = $itemBaseOriginalPrice - $rule->getDiscountAmount();
                $itemOriginalPrice = $this->rulesProductHelper->getItemOriginalPrice($item);
                $originalAmount = $itemOriginalPrice - $quoteAmount;
                $discountData->setAmount($itemQty * $quoteAmount);
                $discountData->setBaseAmount($itemQty * $baseAmount);
                $discountData->setOriginalAmount($itemQty * $originalAmount);
                $discountData->setBaseOriginalAmount($itemQty * $baseOriginalAmount);
            }
        }

        return $discountData;
    }
}
