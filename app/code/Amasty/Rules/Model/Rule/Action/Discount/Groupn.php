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

class Groupn extends AbstractRule
{
    const RULE_VERSION = '1.0.0';

    const DEFAULT_SORT_ORDER = 'asc';

    public static $cachedDiscount = [];

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data|mixed
     */
    public function calculate($rule, $item, $qty)
    {
        $this->beforeCalculate($rule, $item, $qty);
        $discountData = $this->calculateDiscount($rule, $item);
        $this->afterCalculate($discountData, $rule, $item);
        return $discountData;
    }

    /**
     * @param $rule
     * @param $item
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data
     */
    protected function calculateDiscount($rule, $item)
    {
        $ruleId = $this->getRuleId($rule);

        if (!array_key_exists($ruleId, self::$cachedDiscount)) {
            $this->calculateDiscountForRule($item, $rule);
        }

        $discountData = isset(self::$cachedDiscount[$ruleId][$item->getId()])
            ? self::$cachedDiscount[$ruleId][$item->getId()]
            : $this->discountFactory->create();

        return $discountData;
    }

    /**
     * @param $item
     * @param $rule
     * @return $this
     */
    protected function calculateDiscountForRule($item, $rule)
    {
        $allItems = $this->getSortedItems($item->getAddress(), $rule, $this->getSortOrder($rule, self::DEFAULT_SORT_ORDER));

        $totalPrice = $this->getItemsPrice($allItems);

        if ($totalPrice < $rule->getDiscountAmount()) {
            return $this;
        }
        $this->calculateDiscountForEachGroup($rule, $allItems);
    }

    /**
     * @param $rule
     * @param $allItems
     */
    protected function calculateDiscountForEachGroup($rule, $allItems)
    {
        $step = (int)$rule->getDiscountStep() == 0 ? 1 : (int)$rule->getDiscountStep();

        while (count($allItems) >= $step) {
            $groupItems = array_slice($allItems, 0, $step);
            $groupItemsPrice = $this->getItemsPrice($groupItems);

            if ($groupItemsPrice < $rule->getDiscountAmount()) {
                $firstItem = array_shift($allItems);
                unset($firstItem);
            } else {
                $this->calculateDiscountForItems($groupItemsPrice, $rule, $groupItems, $rule->getDiscountAmount());
                $count = 0;

                foreach ($allItems as $i => $item) {
                    if ($count >= $step) {
                        break;
                    }

                    unset($allItems[$i]);
                    $count++;
                }
            }
        }
    }

    /**
     * @param $totalPrice
     * @param $rule
     * @param $itemsForSet
     * @param $quoteAmount
     */
    protected function calculateDiscountForItems($totalPrice, $rule, $itemsForSet, $quoteAmount)
    {
        $ruleId = $this->getRuleId($rule);
        foreach ($itemsForSet as $item) {
            $discountData = $this->discountFactory->create();

            $baseItemPrice = $this->rulesProductHelper->getItemBasePrice($item);
            $baseItemOriginalPrice = $this->rulesProductHelper->getItemBaseOriginalPrice($item);

            $percentage = $baseItemPrice / $totalPrice;
            $baseDiscount = $baseItemPrice - $quoteAmount * $percentage;
            $itemDiscount = $this->priceCurrency->convert($baseDiscount, $item->getQuote()->getStore());
            $baseOriginalDiscount = $baseItemOriginalPrice - $quoteAmount * $percentage;
            $originalDiscount = ($baseItemOriginalPrice / $baseItemPrice) *
                $this->priceCurrency->convert($baseOriginalDiscount, $item->getQuote()->getStore());

            if (!isset(self::$cachedDiscount[$ruleId][$item->getId()])) {
                $discountData->setAmount($itemDiscount);
                $discountData->setBaseAmount($baseDiscount);
                $discountData->setOriginalAmount($originalDiscount);
                $discountData->setBaseOriginalAmount($baseOriginalDiscount);
            } else {
                $cachedItem = self::$cachedDiscount[$ruleId][$item->getId()];
                $discountData->setAmount($itemDiscount + $cachedItem->getAmount());
                $discountData->setBaseAmount($baseDiscount + $cachedItem->getBaseAmount());
                $discountData->setOriginalAmount($originalDiscount + $cachedItem->getOriginalAmount());
                $discountData->setBaseOriginalAmount($baseOriginalDiscount + $cachedItem->getBaseOriginalAmount());
            }

            self::$cachedDiscount[$ruleId][$item->getId()] = $discountData;
        }

    }

    /**
     * @param $items
     * @return float|int
     */
    protected function getItemsPrice($items)
    {
        $totalPrice = 0;
        foreach ($items as $item) {
            $totalPrice += $this->validator->getItemBasePrice($item);
        }

        return $totalPrice;
    }

}
