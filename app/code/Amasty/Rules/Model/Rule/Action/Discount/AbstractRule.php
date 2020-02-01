<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Model\Rule\Action\Discount;

use Amasty\Rules\Api\Data\RuleInterface;
use Amasty\Rules\Helper as amHelper;
use Magento\SalesRule\Model\Rule\Action\Discount as Discount;
use Magento\Store\Model\StoreManagerInterface;

abstract class AbstractRule extends Discount\AbstractDiscount
{
    const ASC_SORT = 'asc';

    const DESC_SORT = 'desc';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Amasty\Rules\Helper\Product
     */
    protected $rulesProductHelper;

    /**
     * @var \Amasty\Rules\Helper\Data
     */
    protected $rulesDataHelper;

    /**
     * @var \Amasty\Rules\Helper\Discount
     */
    protected $rulesDiscountHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    protected $itemsWithDiscount = null;

    /**
     * @var \Amasty\Rules\Model\ConfigModel
     */
    private $configModel;

    /**
     * @var \Amasty\Rules\Model\RuleResolver
     */
    protected $ruleResolver;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoriesCollection;

    public function __construct(
        \Magento\SalesRule\Model\Validator $validator,
        Discount\DataFactory $discountDataFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        \Amasty\Rules\Helper\Product $rulesProductHelper,
        \Amasty\Rules\Helper\Data $rulesDataHelper,
        \Amasty\Rules\Helper\Discount $rulesDiscountHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Amasty\Rules\Model\ConfigModel $configModel,
        \Amasty\Rules\Model\RuleResolver $ruleResolver,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoriesCollection
    ) {
        parent::__construct($validator, $discountDataFactory, $priceCurrency);
        $this->_objectManager = $objectManager;
        $this->storeManager = $storeManager;
        $this->rulesProductHelper = $rulesProductHelper;
        $this->rulesDataHelper = $rulesDataHelper;
        $this->customerSession = $customerSession;
        $this->rulesDiscountHelper = $rulesDiscountHelper;
        $this->configModel = $configModel;
        $this->ruleResolver = $ruleResolver;
        $this->categoriesCollection = $categoriesCollection;
    }

    /**
     * @param \Magento\SalesRule\Model\Data\Rule | \Magento\SalesRule\Model\Rule $rule
     *
     * @return int|null
     */
    protected function getRuleId($rule)
    {
        return $this->ruleResolver->getLinkId($rule);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param string $order
     *
     * @return array
     */
    protected function getSortedItems($address, $rule, $order)
    {
        $items = $this->getAllItems($address);
        $items = $this->validateItems($items, $rule);
        $items = $this->splitItemsWithQty($items);
        $items = $this->sortItemsByPrice($items, $order);

        return $items;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $address
     *
     * @return mixed
     */
    protected function getAllItems($address)
    {
        $items = $address->getAllItems();
        return $items;
    }

    /**
     * @param array $items
     * @param /Magento/SalesRule/Model/Rule $rule
     *
     * @return array
     */
    protected function validateItems($items, $rule)
    {
        $resItems = [];
        $amrulesId = 1;

        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($items as $item) {
            if ($this->skip($rule, $item)) {
                continue;
            }

            if ($item->getParentItem()) {
                continue;
            }

            if ($this->validator->getItemBasePrice($item) != 0) {
                if (!$rule->getActions()->validate($item)) {
                    $childItems = $item->getChildren();
                    $isContinue = true;

                    if (!empty($childItems)) {
                        foreach ($childItems as $childItem) {
                            if ($rule->getActions()->validate($childItem)) {
                                $isContinue = false;

                                break;
                            }
                        }
                    }
                    if ($isContinue) {
                        continue;
                    }
                }

                $item->setAmrulesId($amrulesId);
                $resItems[] = $item;
                $amrulesId++;
            }
        }

        return $resItems;
    }

    /**
     * @param array $items
     *
     * @return array
     */
    protected function splitItemsWithQty($items)
    {
        $resItems = [];
        foreach ($items as $item) {
            $qty = $item->getQty();
            for ($i=0;$i<$qty;$i++) {
                $resItems[] = $item;
            }
        }
        return $resItems;
    }

    /**
     * @param array $items
     * @param string $order
     *
     * @return mixed
     */
    protected function sortItemsByPrice($items, $order)
    {
        usort($items, [$this, $order . "Sort"]);
        return $items;
    }

    /**
     * @param $item1
     * @param $item2
     *
     * @return int
     */
    protected function ascSort($item1, $item2)
    {
        return $this->validator->getItemBasePrice($item1) > $this->validator->getItemBasePrice($item2);
    }

    /**
     * @param $item1
     * @param $item2
     *
     * @return int
     */
    protected function descSort($item1, $item2)
    {
        return $this->validator->getItemBasePrice($item1) < $this->validator->getItemBasePrice($item2);
    }

    /**
     * @param array $items
     *
     * @return array
     */
    protected function getItemsId($items)
    {
        $itemsId = [];
        foreach ($items as $item) {
            $itemsId[] = $item->getAmrulesId();
        }
        return $itemsId;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param float $step
     * @param float $i
     * @param float $currQty
     * @param float $qty
     *
     * @param null $eachNCounter
     * @return bool
     */
    protected function skipBySteps($rule, $step, $i, $currQty, $qty, $eachNCounter = null)
    {
        $eachN = [
            amHelper\Data::TYPE_EACH_N,
            amHelper\Data::TYPE_EACH_N_FIXED,
            amHelper\Data::TYPE_EACH_N_FIXDISC,
        ];
        $eachProdAfterN = [
            amHelper\Data::TYPE_EACH_M_AFT_N_PERC,
            amHelper\Data::TYPE_EACH_M_AFT_N_DISC,
            amHelper\Data::TYPE_EACH_M_AFT_N_FIX
        ];
        $simpleAction = $rule->getSimpleAction();

        if ($i === 0 && in_array($simpleAction, $eachProdAfterN)) {
            return false;
        }
        if ($step > 1 && $eachNCounter % $step && in_array($simpleAction, $eachN)) {
            return true;
        }
        if ($step > 1 && ($i % $step) && in_array($simpleAction, $eachProdAfterN)) {
            return true;
        }

        $typeGroupN = amHelper\Data::TYPE_GROUP_N;
        $typeGroupNDisc = amHelper\Data::TYPE_GROUP_N_DISC;

        // introduce limit for each N with discount or each N with fixed.
        if ((($currQty >= $qty) && ($simpleAction !== $typeGroupN) && ($simpleAction !== $typeGroupNDisc))
            || (($rule->getDiscountQty() <= $currQty) && ($rule->getDiscountQty()) && (($simpleAction === $typeGroupN)
                    || ($simpleAction === $typeGroupNDisc)))) {
            return true;
        }
    }

    /**
     * @param array $array
     * @param float $value
     *
     * @return float
     */
    public function getArrayValueCount($array, $value)
    {
        $values = array_count_values($array);
        return $values[$value];
    }

    /**
     * @param float $qty
     * @param \Magento\SalesRule\Model\Rule $rule
     * @return float
     */
    public function ruleQuantity($qty, $rule)
    {
        $discountQty = 1;
        $discountStep = (int)$rule->getDiscountStep();

        $arrayWithEachRules = ['eachmaftn_fixdisc', 'eachmaftn_perc', 'eachmaftn_fixprice'];

        if ($discountStep) {
            if (in_array($rule->getSimpleAction(), $arrayWithEachRules)) {
                $discountQty = round($qty / $discountStep);
            } else {
                $discountQty = floor($qty / $discountStep);
            }

            $maxDiscountQty = (int)$rule->getDiscountQty();
            if (!$maxDiscountQty) {
                $maxDiscountQty = $qty;
            }

            $discountQty = min($discountQty, $maxDiscountQty);
        }
        return $discountQty;
    }

    /**
     * @param array $allItems
     * @param \Magento\SalesRule\Model\Rule $rule
     * @return array
     */
    public function skipEachN($allItems, $rule)
    {
        $discountStep = (int)$rule->getDiscountStep();
        $step = $discountStep !== '' ? $discountStep : (int)$rule->getAmrulesRule()->getEachm();
        if ($step <= 0) {
            $step = 1;
        }

        $currQty = 0;
        $resItems = [];
        $itemsId = $this->getItemsId($allItems);
        $ruleQty = $this->ruleQuantity(count($itemsId), $rule);
        $eachN =  1;

        foreach ($allItems as $i => $allItem) {
            if ($this->skipBySteps($rule, $step, $i, $currQty, $ruleQty, $eachN)) {
                $eachN++;
                continue;
            }
            $eachN++;
            $currQty++;
            $resItems[] = $allItem;
        }

        return $resItems;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     *
     * @return int
     */
    protected function getItemQty($item)
    {
        if (!$item) {
            return 1;
        }
        //comatibility with CE 1.3 version
        return $item->getTotalQty() ? $item->getTotalQty() : $item->getQty();
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     *
     * @return int
     */
    protected function getItemQtyBundle($item)
    {
        if (!$item) {
            return 1;
        }
        //comatibility with CE 1.3 version
        return $item->getQty() ? $item->getQty() : $item->getTotalQty();
    }

    /**
     * @param $prices
     * @param $qty
     *
     * @return bool
     */
    public function hasDiscountItems($prices, $qty)
    {
        if (!$prices || $qty < 1) {
            return false;
        }

        return true;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return bool
     */
    public function beforeCalculate($rule, $item, $qty)
    {
        $this->rulesProductHelper->setRule($rule);
        if (!$rule->hasData(RuleInterface::RULE_NAME)) {
            $this->ruleResolver->getSpecialPromotions($rule);
        }

        return true;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule\Action\Discount\Data Data
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return bool
     */
    public function afterCalculate($discountData, $rule, $item)
    {
        $this->rulesDiscountHelper->setDiscount(
            $rule,
            $discountData,
            $item->getQuote()->getStore(),
            $item->getId()
        );

        return true;
    }

    /**
     * determines if we should skip the items with special price or other (in futeure) conditions
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return bool
     */
    public function skip($rule, $item)
    {
        if ($rule->getSimpleAction() == 'cart_fixed') {
            return false;
        }

        $website_id = $this->storeManager->getWebsite()->getId();
        $groupId = $this->customerSession->getCustomerGroupId();

        $skipTierPrice = $this->configModel->getSkipTierPrice();

        $origProduct = $item->getProduct();
        $tierPrices = $origProduct->getTierPrice();
        if ($skipTierPrice) {
            foreach ($tierPrices as $tierPrice) {
                if (($tierPrice['cust_group'] == $groupId ||
                        \Magento\Customer\Model\GroupManagement::CUST_GROUP_ALL == $tierPrice['cust_group'])
                    && $item->getQty() >= $tierPrice['price_qty'] && $website_id == $tierPrice['website_id']) {
                    return true;
                }
            }
        }

        if ($item->getProductType() == 'bundle') {
            return false;
        }

        if ($this->skipWithDiscount($item)) {
            return true;
        }

        if ($this->checkSkipRule($rule, $item)) {
            return true;
        }

        return false;
    }

    /**
     * determines if we should skip item by skip rule setting
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return bool
     */
    protected function checkSkipRule($rule, $item)
    {
        $skipSpecialPrice = $this->configModel->getSkipSpecialPrice();

        switch ($rule->getAmrulesRule()->getData('skip_rule')) {
            case 0:
                if ($skipSpecialPrice) {
                    if (in_array($item->getProductId(), $this->getItemsWithDiscount($item))) {
                        return true;
                    }
                }
                break;
            case 1:
                if (in_array($item->getProductId(), $this->getItemsWithDiscount($item))) {
                    return true;
                }
                break;
            case 3:
                $price = $item->getDiscountCalculationPrice();
                ($price !== null) ? $price = $item->getBaseDiscountCalculationPrice() : $price = $item->getBaseCalculationPrice();
                $price -= $item->getBaseDiscountAmount();
                if ($item->getProduct()->getPrice() > $price) {
                    return true;
                }
                break;
        }
        return false;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     *
     * @return bool
     */
    protected function skipWithDiscount($item)
    {
        $skipSpecialConfigurable = $this->configModel->getSkipSpecialPriceConfigurable();

        if ($skipSpecialConfigurable && $item->getProductType() == "configurable" && !empty($this->getItemsWithDiscount($item))) {
            foreach ($item->getChildren() as $child) {
                if (in_array($child->getProductId(), $this->getItemsWithDiscount($item))) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     *
     * @return int[]
     */
    protected function getItemsWithDiscount($item)
    {
        if ($this->itemsWithDiscount === null) {
            $productIds = $this->itemsWithDiscount = [];
            $address = $item->getAddress();

            foreach ($this->getAllItems($address) as $addressItem) {
                $productIds[] = $addressItem->getProductId();
            }

            if (!$productIds) {
                return $this->itemsWithDiscount;
            }

            /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
            $productCollection = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');

            $productsCollection = $productCollection
                ->addPriceData()
                ->addAttributeToFilter('entity_id', ['in' => $productIds]);

            /** @var \Magento\Catalog\Model\Product $product */
            foreach ($productsCollection->getItems() as $product) {
                $product->setPriceCalculation($product->getDataByKey('final_price') === null);
                if ($product->getPrice() > $product->getFinalPrice()) {
                    $this->itemsWithDiscount[] = $product->getId();
                }
                unset($product);
            }
        }

        return $this->itemsWithDiscount;
    }

    /**
     * @param $item
     * @param $itemsId
     * @param $allItem
     * @param $qty
     * @return bool
     */
    protected function isContinueEachmaftnCalculation($item, $itemsId, $allItem, $qty)
    {
        return in_array($item->getAmrulesId(), $itemsId) && $allItem->getAmrulesId()===$item->getAmrulesId() && $qty > 0;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param string $defaultSortOrder
     * @return mixed
     */
    public function getSortOrder($rule, $defaultSortOrder)
    {
        $amRule = $rule->getAmrulesRule();
        if ($amRule) {
            $order = $amRule->getApplyDiscountTo() ? $amRule->getApplyDiscountTo() : $defaultSortOrder;
        } else {
            $order = self::ASC_SORT;
        }

        return $order;
    }
}
