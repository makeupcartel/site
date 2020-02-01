<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Plugin\Condition;

class Combine
{
    /**
     * @var \Amasty\Rules\Helper\Data
     */
    private $rulesDataHelper;

    /**
     * @var \Amasty\Rules\Model\RuleResolver
     */
    private $ruleResolver;

    public function __construct(
        \Amasty\Rules\Helper\Data $rulesDataHelper,
        \Amasty\Rules\Model\RuleResolver $ruleResolver
    ) {
        $this->rulesDataHelper = $rulesDataHelper;
        $this->ruleResolver = $ruleResolver;
    }

    public function aroundValidate(
        \Magento\Rule\Model\Condition\Combine $subject,
        \Closure $proceed,
        $type
    ) {

        if ($type instanceof \Magento\Quote\Model\Quote\Item) {
            $discountItem = $this->checkActionItem($subject->getRule(), $type);
            if ($discountItem) {
                return true;
            }
        }

        return $proceed($type);
    }

    protected function checkActionItem($rule, $item)
    {
        $action = $rule->getSimpleAction();

        if (strpos($action, "buyxget") !== false) {
            $this->ruleResolver->getSpecialPromotions($rule);

            $promoCats = $this->rulesDataHelper->getRuleCats($rule);
            $promoSku  = $this->rulesDataHelper->getRuleSkus($rule);
            $itemSku   = $item->getSku();
            $itemCats  = $item->getCategoryIds();

            if (!$itemCats) {
                $itemCats = $item->getProduct()->getCategoryIds();
            }

            $parent = $item->getParentItem();

            //if (Mage::helper('amrules')->isConfigurablePromoItem($object,$promoSku)) return true;

            if ($parent) {
                $parentType = $parent->getProductType();
                if ($parentType == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
                    $itemSku  = $item->getParentItem()->getProduct()->getSku();
                    $itemCats = $item->getParentItem()->getProduct()->getCategoryIds();
                }
            }

            if (in_array($itemSku, $promoSku)) {
                return true;
            }

            if (!is_null($itemCats) && array_intersect($promoCats, $itemCats)) {
                return true;
            }
        }

        return false;
    }
}
