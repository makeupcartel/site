<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Model;

/**
 * Promo Items Registry
 */
class Registry
{
    /**
     * Product types available for auto add to cart
     */
    const AUTO_ADD_PRODUCT_TYPES = ['simple', 'virtual', 'downloadable'];

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionFactory;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Amasty\Promo\Helper\Item
     */
    private $promoItemHelper;

    /**
     * @var \Amasty\Promo\Helper\Messages
     */
    private $promoMessagesHelper;

    /**
     * @var \Magento\Store\Model\Store
     */
    private $store;

    /**
     * @var array
     */
    private $fullDiscountItems;

    /**
     * @var \Amasty\Promo\Model\Product
     */
    private $product;

    /**
     * @var \Amasty\Promo\Model\DiscountCalculator
     */
    private $discountCalculator;

    /**
     * @var ItemRegistry\PromoItemRegistry
     */
    private $promoItemRegistry;

    public function __construct(
        \Magento\Checkout\Model\Session $resourceSession,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Promo\Helper\Item $promoItemHelper,
        \Amasty\Promo\Helper\Messages $promoMessagesHelper,
        \Magento\Store\Model\Store $store,
        \Amasty\Promo\Model\Product $product,
        \Amasty\Promo\Model\DiscountCalculator $discountCalculator,
        ItemRegistry\PromoItemRegistry $promoItemRegistry
    ) {
        $this->checkoutSession = $resourceSession;
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        $this->promoItemHelper = $promoItemHelper;
        $this->promoMessagesHelper = $promoMessagesHelper;
        $this->store = $store;
        $this->fullDiscountItems = [];
        $this->product = $product;
        $this->discountCalculator = $discountCalculator;
        $this->promoItemRegistry = $promoItemRegistry;
    }

    /**
     * @param string|array $sku
     * @param int $qty
     * @param int $ruleId
     * @param array $discountData
     * @param int $type
     * @param string $discountAmount
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addPromoItem($sku, $qty, $ruleId, $discountData, $type, $discountAmount)
    {
        $discountData = $this->getCurrencyDiscount($discountData);

        $autoAdd = false;

        if (is_array($sku) && count($sku) === 1) {
            // if rule with behavior 'one of' have only single product item,
            // then behavior should be the same as rule 'all'
            $sku = $sku[0];
        }

        if (!is_array($sku)) {
            $productQty = $this->product->getProductQty($sku);

            if ($productQty !== false && $qty > $productQty) {
                $qty = $productQty;
            }

            if ($this->discountCalculator->isEnableAutoAdd($discountData)) {
                $collection = $this->collectionFactory->create()
                    ->addAttributeToSelect(['name', 'status', 'required_options'])
                    ->addAttributeToFilter('sku', $sku)
                    ->setPage(1, 1);

                $product = $collection->getFirstItem();

                $currentWebsiteId = $this->storeManager->getWebsite()->getId();
                if (!is_array($product->getWebsiteIds())
                    || !in_array($currentWebsiteId, $product->getWebsiteIds())
                ) {
                    // Ignore products from other websites
                    return;
                }

                if (!$product || !$product->isInStock() || !$product->isSalable()) {
                    $this->promoMessagesHelper->addAvailabilityError($product);
                } else {
                    if (in_array($product->getTypeId(), static::AUTO_ADD_PRODUCT_TYPES)
                        && !$product->getTypeInstance(true)->hasRequiredOptions($product)
                    ) {
                        $autoAdd = true;
                    }
                }
            }

            $item = $this->promoItemRegistry->registerItem(
                $sku,
                $qty,
                $ruleId,
                $type,
                $discountData['minimal_price'],
                $discountData['discount_item'],
                $discountAmount
            );

            $item->setAutoAdd($autoAdd);
        } else {
            foreach ($sku as $skuValue) {
                $this->promoItemRegistry->registerItem(
                    $skuValue,
                    $qty,
                    $ruleId,
                    $type,
                    $discountData['minimal_price'],
                    $discountData['discount_item'],
                    $discountAmount
                );
            }
        }

        if ($this->discountCalculator->isFullDiscount($discountData)) {
            if (!is_array($sku)) {
                $sku = [$sku];
            }

            foreach ($sku as $itemSku) {
                $this->fullDiscountItems[$itemSku]['rule_ids'][$ruleId] = $ruleId;
            }
        }

        $this->checkoutSession->setAmpromoFullDiscountItems($this->fullDiscountItems);
    }

    /**
     * @param array $discountData
     * @return array
     */
    private function getCurrencyDiscount($discountData)
    {
        preg_match('/^-*\d+.*\d*$/', $discountData['discount_item'], $discount);
        if (isset($discount[0]) && is_numeric($discount[0])) {
            $discountData['discount_item'] = $discount[0] * $this->store->getCurrentCurrencyRate();
        }

        return $discountData;
    }

    /**
     * Set reserved quantity according cart products
     */
    public function updatePromoItemsReservedQty($quote = null)
    {
        if (!$quote) {
            $quote = $this->checkoutSession->getQuote();
        }
        $this->promoItemRegistry->resetQtyReserve();

        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($quote->getAllVisibleItems() as $item) {
            if ($this->promoItemHelper->isPromoItem($item)) {
                $sku = $item->getProduct()->getData('sku');
                $ruleId = $this->promoItemHelper->getRuleId($item);
                $promoItem = $this->promoItemRegistry->getItemBySkuAndRuleId($sku, $ruleId);
                if (!$promoItem) {
                    continue;
                }

                $this->promoItemRegistry->assignQtyToItem(
                    $item->getQty(),
                    $promoItem,
                    ItemRegistry\PromoItemRegistry::QTY_ACTION_RESERVE
                );
            }
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     */
    public function deleteProduct($item)
    {
        $fullDiscountItems = $this->checkoutSession->getAmpromoFullDiscountItems();
        $sku = $item->getProduct()->getSku();

        $item = $this->promoItemRegistry->getItemBySkuAndRuleId($sku, $this->promoItemHelper->getRuleId($item));
        if ($item) {
            $item->isDeleted(true);
            $item->setReservedQty(0);
        }

        if (isset($fullDiscountItems[$sku])) {
            unset($fullDiscountItems[$sku]);
            $this->checkoutSession->setAmpromoFullDiscountItems($fullDiscountItems);
        }
    }
}
