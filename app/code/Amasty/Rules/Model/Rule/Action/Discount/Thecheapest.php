<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Model\Rule\Action\Discount;

class Thecheapest extends AbstractRule
{
    const RULE_VERSION = '1.0.0';
    const DEFAULT_SORT_ORDER = 'asc';

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data Data
     */
    public function calculate($rule, $item, $qty)
    {
        $this->beforeCalculate($rule, $item, $qty);
        $rulePercent = min(100, $rule->getDiscountAmount());
        $discountData = $this->_calculate($rule, $item, $rulePercent);
        $this->afterCalculate($discountData, $rule, $item);

        return $discountData;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $rulePercent
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data Data
     */
    protected function _calculate($rule, $item, $rulePercent)
    {
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $discountData = $this->discountFactory->create();
        $itemsId = $this->getAllowedItemsIds($item->getAddress(), $rule);

        if (in_array($item->getAmrulesId(), $itemsId)) {
            $itemQty = $this->getArrayValueCount($itemsId, $item->getAmrulesId());
            /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
            $discountData = $this->calculateDiscount($item, $itemQty, $rulePercent);

            if (!$rule->getDiscountQty() || $rule->getDiscountQty() > $itemQty) {
                $discountPercent = min(100, $item->getDiscountPercent() + $rulePercent);
                $item->setDiscountPercent($discountPercent);
            }
        }

        return $discountData;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param \Magento\SalesRule\Model\Rule $rule
     *
     * @return array
     */
    private function getAllowedItemsIds($address, $rule)
    {
        $allItems = $this->getSortedItems($address, $rule, static::DEFAULT_SORT_ORDER);
        $sliceQty = $this->ruleQuantity(count($allItems), $rule);
        $allItems = array_slice($allItems, 0, $sliceQty);

        return $this->getItemsId($allItems);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $itemQty
     * @param float $rulePercent
     *
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     */

    private function calculateDiscount($item, $itemQty, $rulePercent)
    {
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $discountData = $this->discountFactory->create();

        $itemPrice = $this->rulesProductHelper->getItemPrice($item);
        $baseItemPrice = $this->rulesProductHelper->getItemBasePrice($item);
        $itemOriginalPrice = $this->rulesProductHelper->getItemOriginalPrice($item);
        $baseItemOriginalPrice = $this->rulesProductHelper->getItemBaseOriginalPrice($item);

        $rulePct = $rulePercent / 100;
        $amount = $itemQty * $itemPrice * $rulePct;
        $baseOriginalAmount = $itemQty * $baseItemOriginalPrice * $rulePct;
        $baseAmount = $itemQty * $baseItemPrice * $rulePct;
        $originalAmount = $itemQty * $itemOriginalPrice * $rulePct;

        $discountData->setAmount($amount);
        $discountData->setBaseAmount($baseAmount);
        $discountData->setOriginalAmount($originalAmount);
        $discountData->setBaseOriginalAmount($baseOriginalAmount);

        return $discountData;
    }
}
