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

class EachmaftnFixdisc extends AbstractRule
{
    const RULE_VERSION = '1.0.0';
    const DEFAULT_SORT_ORDER = 'desc';

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data Data
     */
    public function calculate($rule, $item, $qty)
    {
        $this->beforeCalculate($rule, $item, $qty);
        $discountData = $this->_calculate($rule, $item);
        $this->afterCalculate($discountData, $rule, $item);
        return $discountData;
    }

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
        $qty = max(0, $rule->getDiscountQty()); // qty should be positive
        if ($qty) {
            $qty = min($qty, count($allItems));
        } else {
            $qty = count($allItems);
        }
        $offset = (int)$rule->getAmrulesRule()->getEachm();
        if ($offset < 0) {
            $offset = 0;
        }
        $offset = min($offset, count($allItems));
        $allItems = array_slice($allItems, $offset);
        $allItems = $this->skipEachN($allItems, $rule);
        $itemsId = $this->getItemsId($allItems);
        /** @var \Magento\Quote\Model\Quote\Item\AbstractItem $allItem */
        foreach ($allItems as $i => $allItem) {
            if ($this->isContinueEachmaftnCalculation($item, $itemsId, $allItem, $qty)) {
                $itemQty = $this->getArrayValueCount($itemsId, $item->getAmrulesId());
                $quoteAmount = $this->priceCurrency->convert($rule->getDiscountAmount(), $item->getQuote()->getStore());
                $discountData->setAmount($itemQty * $quoteAmount);
                $discountData->setBaseAmount($itemQty * $rule->getDiscountAmount());
                $discountData->setOriginalAmount($itemQty * $quoteAmount);
                $discountData->setBaseOriginalAmount($itemQty * $rule->getDiscountAmount());
                $qty--;
            }
        }
        return $discountData;
    }
}
