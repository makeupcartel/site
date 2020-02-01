<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */

namespace Amasty\Rules\Model\Rule\Action\Discount;

abstract class Buyxgety extends AbstractRule
{
    protected $_passedItems = [];
    const DEFAULT_SORT_ORDER = 'asc';

    /**
     * @param $address
     * @param $rule
     * @return array
     */
    public function getTriggerElements($address, $rule)
    {
        // find all X (trigger) elements
        $arrX = [];
        foreach ($this->getSortedItems($address, $rule, self::DEFAULT_SORT_ORDER) as $item) {
            if ($item->getParentItemId()) {
                continue;
            }

            if (!$item->getAmrulesId()) {
                continue;
            }
            $promoCats = $this->rulesDataHelper->getRuleCats($rule);
            $promoSku  = $this->rulesDataHelper->getRuleSkus($rule);
            //if ( Mage::helper('amrules')->isConfigurablePromoItem($item,$promoSku)  ) continue;

            if (in_array($item->getSku(), $promoSku)) {
                continue;
            }
            if (!$promoSku) {
                $itemCats = $item->getCategoryIds();
                if (!$itemCats) {
                    $itemCats = $item->getProduct()->getCategoryIds();
                }
                if (!is_null($itemCats) && array_intersect($promoCats, $itemCats)) {
                    continue;
                }
            }
            $arrX[$item->getAmrulesId()] = $item;
        }

        return $arrX;
    }

    /**
     * @param $arrX
     * @return int
     */
    public function getTriggerElementQty($arrX)
    {
        $realQty = 0;
        foreach ($arrX as $el) {
            $realQty += $this->getItemQty($el);
        }

        return $realQty;
    }

    /**
     * @param $rule
     * @param $item
     * @return bool
     */
    public function isDiscountedItem($rule, $item)
    {
        $product = $item->getProduct();
        // for configurable product we need to use the child
        if ($item->getHasChildren() && $item->getProductType() == 'configurable') {
            foreach ($item->getChildren() as $child) {
                // one iteration only
                $product = $child->getProduct();
                // can work for credit cards, but does not work with PayPal, so it is commented out
                //$categoryIds = array_merge($product->getCategoryIds(), $item->getProduct()->getCategoryIds());
                //$product->setCategoryIds($categoryIds);
            }
        }

        $cats = $this->rulesDataHelper->getRuleCats($rule);
        $sku  = $this->rulesDataHelper->getRuleSkus($rule);

        $currentSku  = $product->getSku();
        $currentCats = $product->getCategoryIds();

        $parent = $item->getParentItem();

        //if ( Mage::helper('amrules')->isConfigurablePromoItem($item,$sku)  ) return true;

        if (isset($parent)) {
            $parentType = $parent->getProductType();
            if ($parentType == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
                $currentSku  = $item->getParentItem()->getProduct()->getSku();
                $currentCats = $item->getParentItem()->getProduct()->getCategoryIds();
            }
        }

        if (!in_array($currentSku, $sku) && !array_intersect($cats, $currentCats)) {
            return false;
        }

        return true;
    }

    /**
     * @param $item
     * @param $arrX
     * @param $passed
     * @return bool
     */
    public function canProcessItem($item, $arrX, $passed)
    {
        if (!$item->getAmrulesId()) {
            return false;
        }
        //do not apply discont on triggers
        if (isset($arrX[$item->getAmrulesId()])) {
            return false;
        }

        if (in_array($item->getAmrulesId(), $passed)) {
            return false;
        }

        return true;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param $realQty
     * @return float|int|mixed
     */
    protected function getNQty($rule, $realQty)
    {
        if ($rule->getDiscountStep() > $realQty) {
            return 0;
        } else {
            $step = $rule->getDiscountStep();
            $step = max(1, $step);
            $count = floor($realQty / $step) * $rule->getAmrulesRule()->getData('nqty');
            $discountQty = $rule->getDiscountQty();
            if ($discountQty) {
                $nqty = min($count, $discountQty);
            } else {
                $nqty = $count;
            }

            if ($nqty <= 0) {
                $nqty = 1;
            }

            return $nqty;
        }
    }
}
