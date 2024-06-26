<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Helper;

/**
 * Helper probably will be moved/separated
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Allowed product types for precess as Free Gift (Promo Item)
     */
    const ALLOWED_PRODUCT_TYPES = [
        \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
        \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
        \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE,
        'giftcard',//EE
    ];

    /**
     * @var \Amasty\Promo\Model\Registry
     */
    protected $promoRegistry;

    /**
     * @var \Amasty\Promo\Helper\Messages
     */
    protected $promoMessagesHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection|null|false
     */
    protected $productsCache = null;

    /**
     * @var \Amasty\Promo\Model\Product
     */
    private $product;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Amasty\Promo\Model\ItemRegistry\PromoItemRegistry
     */
    private $promoItemRegistry;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Amasty\Promo\Model\Registry $promoRegistry,
        \Amasty\Promo\Helper\Messages $promoMessagesHelper,
        \Amasty\Promo\Model\Product $product,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Amasty\Promo\Model\ItemRegistry\PromoItemRegistry $promoItemRegistry
    ) {
        parent::__construct($context);
        $this->promoRegistry = $promoRegistry;
        $this->promoMessagesHelper = $promoMessagesHelper;
        $this->product = $product;
        $this->collectionFactory = $collectionFactory;
        $this->promoItemRegistry = $promoItemRegistry;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|false
     */
    public function getNewItems()
    {
        if ($this->productsCache === null) {
            $this->productsCache = false;
            $this->promoRegistry->updatePromoItemsReservedQty();
            if (!$allowedSku = $this->promoItemRegistry->getAllowedSkus()) {
                return false;
            }
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $products */
            $products = $this->collectionFactory->create()
                ->addAttributeToSelect(['name', 'small_image', 'status', 'visibility'])
                ->addFieldToFilter('sku', ['in' => $allowedSku])
                ->setFlag('has_stock_status_filter', false);

            /** @var \Magento\Catalog\Model\Product $product */
            foreach ($products as $key => $product) {
                if (!in_array($product->getTypeId(), static::ALLOWED_PRODUCT_TYPES)) {
                    $this->promoMessagesHelper->showMessage(__(
                        "We apologize, but products of type <strong>%1</strong> are not supported",
                        $product->getTypeId()
                    ));

                    $products->removeItemByKey($key);
                }

                if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
                    && (!$product->isSalable() || !$this->product->checkAvailableQty($product, 1))
                ) {
                    $this->promoMessagesHelper->addAvailabilityError($product);

                    $products->removeItemByKey($key);
                }

                foreach ($product->getProductOptionsCollection() as $option) {
                    $option->setProduct($product);
                    $product->addOption($option);
                }
            }

            if ($products->getSize() > 0) {
                $this->productsCache = $products;
            }
        }

        return $this->productsCache;
    }

    /**
     * @return array
     * @deprecated
     */
    public function getAllowedProductQty()
    {
        return $this->getPromoItemsDataArray();
    }

    /**
     * Gat data for popup
     *
     * @return array
     */
    public function getPromoItemsDataArray()
    {
        $promoSkus = $discountData = [];
        $this->promoRegistry->updatePromoItemsReservedQty();
        $qtyByRule = [0 => 0];

        foreach ($this->promoItemRegistry->getAllowedItems() as $promoItemData) {
            $sku = $promoItemData->getSku();
            $ruleId = $promoItemData->getRuleId();
            $itemQty = $promoItemData->getQtyToProcess();
            if ($promoItemData->getRuleType() == \Amasty\Promo\Model\Rule::RULE_TYPE_ONE) {
                // items with rule type 'one of' have qty for group
                // must be before qty fix
                $qtyByRule[$promoItemData->getRuleId()] = $itemQty;
            }

            $itemQty = $this->fixQty($sku, $itemQty);

            if ($promoItemData->getRuleType() == \Amasty\Promo\Model\Rule::RULE_TYPE_ALL) {
                // items with rule type 'all' have qty for each item
                // should be after qty fix
                $qtyByRule[0] += $itemQty;
            }

            $itemData = [
                'discount' => $promoItemData->getDiscountArray(),
                'qty' => $itemQty
            ];
            $discountData[$ruleId]['rule_type'] = $promoItemData->getRuleType();
            $discountData[$ruleId]['discount_amount'] = $promoItemData->getDiscountAmount();
            $discountData[$ruleId]['sku'][$sku] = $itemData;
            $promoSkus[$sku] = &$discountData[$ruleId]['sku'][$sku];
        }

        return [
            'common_qty' => array_sum($qtyByRule),
            'triggered_products' => $discountData,
            'promo_sku' => $promoSkus
        ];
    }

    /**
     * Check available inventory
     *
     * @param string $sku
     * @param int|float $qty
     *
     * @return float|int
     */
    protected function fixQty($sku, $qty)
    {
        $productQty = $this->product->getProductQty($sku);

        if ($productQty !== false && $qty > $productQty) {
            return $productQty;
        }

        return $qty;
    }
}
