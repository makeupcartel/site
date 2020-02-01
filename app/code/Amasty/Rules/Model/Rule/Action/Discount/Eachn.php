<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Model\Rule\Action\Discount;

abstract class Eachn extends AbstractRule
{
    const RULE_VERSION = '1.0.0';
    const DEFAULT_SORT_ORDER = 'desc';
    const USE_FOR_SAME_PRODUCT = 1;

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
     * @param $allItems
     * @param $rule
     * @return array
     */
    public function reduceItems($allItems, $rule)
    {
        $discountStep = (int)$rule->getDiscountStep();
        $step = $discountStep !== '' ? $discountStep : (int)$rule->getAmrulesRule()->getEachm();
        if ($step <= 0) {
            $step = 1;
        }

        $groupedItems = $this->groupItemsBySku($allItems);
        $reducedItems = [];
        foreach ($groupedItems as $group) {
            $count = count($group);
            $group = array_slice($group, $count % $step);
            $reducedItemsByGroup = array_values($group);
            $reducedItems += $reducedItemsByGroup;
        }

        return $reducedItems;
    }

    /**
     * @param $allItems
     * @return array
     */
    private function groupItemsBySku($allItems)
    {
        $groupedItems = [];
        foreach ($allItems as $item) {
            $groupedItems[$item->getSku()][] = $item;
        }

        return $groupedItems;
    }
}
