<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Model\Rule\Action\Discount;

class BuyxgetnPerc extends Buyxgety
{
    const RULE_VERSION = '1.0.0';
    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     *
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
     *
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data Data
     */
    protected function _calculate($rule, $item, $rulePercent)
    {
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $discountData = $this->discountFactory->create();

        // no conditions for Y elements
        if (!$rule->getAmrulesRule()->getPromoCats() && !$rule->getAmrulesRule()->getPromoSkus()) {
            return $discountData;
        }

        $rulePercent /= 100;
        $address = $item->getAddress();
        $arrX = $this->getTriggerElements($address, $rule);
        $realQty = $this->getTriggerElementQty($arrX);
        $maxQty = $this->getNQty($rule, $realQty);
        // find all allowed Y (discounted) elements and calculate total discount
        $passedItems = [];
        $lastId = 0;
        $currQty = 0;
        $allItems = $this->getSortedItems($address, $rule, self::DEFAULT_SORT_ORDER);
        $itemsId = $this->getItemsId($allItems);

        foreach ($allItems as $i => $allItem) {
            if ($currQty >= $maxQty) {
                break;
            }

            // we always skip child items and calculate discounts inside parents
            if (!$this->canProcessItem($allItem, $arrX, $passedItems)) {
                continue;
            }
            // what should we do with bundles when we treat them as
            // separate items
            $passedItems[$allItem->getAmrulesId()] = $allItem->getAmrulesId();

            if (!$this->isDiscountedItem($rule, $allItem)) {
                continue;
            }

            $qty = $this->getItemQty($allItem);

            if (($qty == $currQty) && ($lastId == $item->getAmrulesId())) {
                continue;
            }

            $qty = min($maxQty - $currQty, $qty);
            $currQty += $qty;

            if (in_array($item->getAmrulesId(), $itemsId) && $allItem->getAmrulesId() === $item->getAmrulesId()) {
                $itemPrice = $this->rulesProductHelper->getItemPrice($item);
                $baseItemPrice = $this->rulesProductHelper->getItemBasePrice($item);
                $itemOriginalPrice = $this->rulesProductHelper->getItemOriginalPrice($item);
                $baseItemOriginalPrice = $this->rulesProductHelper->getItemBaseOriginalPrice($item);

                $discountData->setAmount($qty * $itemPrice * $rulePercent);
                $discountData->setBaseAmount($qty * $baseItemPrice * $rulePercent);
                $discountData->setOriginalAmount($qty * $itemOriginalPrice * $rulePercent);
                $discountData->setBaseOriginalAmount($qty * $baseItemOriginalPrice * $rulePercent);

                if (!$rule->getDiscountQty() || $rule->getDiscountQty() > $qty) {
                    $discountPercent = min(100, $item->getDiscountPercent() + $rulePercent * 100);
                    $item->setDiscountPercent($discountPercent);
                }
            }
        }

        return $discountData;
    }
}
