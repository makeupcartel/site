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

class EachnPerc extends Eachn
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
        $allItems = $this->getSortedItems($item->getAddress(), $rule, $this->getSortOrder($rule, self::DEFAULT_SORT_ORDER));
        if ($rule->getAmrulesRule()->getUseFor() == self::USE_FOR_SAME_PRODUCT) {
            $allItems = $this->reduceItems($allItems, $rule);
        }
        $allItems = $this->skipEachN($allItems, $rule);
        $itemsId = $this->getItemsId($allItems);
        $rulePercent = min(100, $rule->getDiscountAmount());
        $_rulePct = $rulePercent / 100.0;

        /** @var \Magento\Quote\Model\Quote\Item\AbstractItem $allItem */
        foreach ($allItems as $i => $allItem) {
            if (in_array($item->getAmrulesId(), $itemsId) && $allItem->getAmrulesId()===$item->getAmrulesId()) {
                $itemPrice = $this->rulesProductHelper->getItemPrice($item);
                $baseItemPrice = $this->rulesProductHelper->getItemBasePrice($item);
                $itemOriginalPrice = $this->rulesProductHelper->getItemOriginalPrice($item);
                $baseItemOriginalPrice = $this->rulesProductHelper->getItemBaseOriginalPrice($item);
                $itemQty = $this->getArrayValueCount($itemsId, $item->getAmrulesId());
                $discountData->setAmount($itemQty * $itemPrice * $_rulePct);
                $discountData->setBaseAmount($itemQty * $baseItemPrice * $_rulePct);
                $discountData->setOriginalAmount($itemQty * $itemOriginalPrice * $_rulePct);
                $discountData->setBaseOriginalAmount($itemQty * $baseItemOriginalPrice * $_rulePct);
                if (!$rule->getDiscountQty() || $rule->getDiscountQty() > $itemQty) {
                    $discountPercent = min(100, $item->getDiscountPercent() + $_rulePct*100);
                    $item->setDiscountPercent($discountPercent);
                }
            }
        }

        return $discountData;
    }
}
