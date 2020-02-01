<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Model;

use Magento\CatalogInventory\Model\Spi\StockStateProviderInterface;

/**
 * Retrieve Product Data
 */
class Product
{
    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    private $state;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface
     */
    private $stockRegistryProvider;

    /**
     * @var StockStateProviderInterface
     */
    private $stockStateProvider;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;

    public function __construct(
        \Magento\CatalogInventory\Api\StockStateInterface $state,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface $stockRegistryProvider,
        StockStateProviderInterface $stockStateProvider,
        \Magento\Checkout\Model\Cart $cart
    ) {
        $this->state = $state;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->stockConfiguration = $stockConfiguration;
        $this->stockRegistryProvider = $stockRegistryProvider;
        $this->stockStateProvider = $stockStateProvider;
        $this->cart = $cart;
    }

    /**
     * @param string $sku
     *
     * @return bool|float|int
     */
    public function getProductQty($sku)
    {
        $qty = 0;

        try {
            /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
            $product = $this->productRepository->get($sku, false, $this->storeManager->getStore()->getId());
            if ($product->getTypeId() === \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
                || $product->getTypeId() === \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
            ) {
                return false;
            }

            if (!$this->isManageStock((int)$product->getId())) {
                return false;
            }

            $qty = $this->state->getStockQty(
                $product->getId(),
                $this->storeManager->getWebsite()->getId()
            );
            $stockItem = $this->stockRegistryProvider->getStockItem(
                $product->getId(),
                $this->storeManager->getWebsite()->getId()
            );

            if ($stockItem->getBackorders()) {
                $qty = $stockItem->getMaxSaleQty();
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->logger->critical($e->getTraceAsString());
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->critical($e->getTraceAsString());
        }

        return $qty;
    }

    /**
     * @param int $productId
     *
     * @return bool
     */
    private function isManageStock($productId)
    {
        $scopeId = $this->stockConfiguration->getDefaultScopeId();
        /** @var \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem */
        $stockItem = $this->stockRegistryProvider->getStockItem($productId, $scopeId);

        return $stockItem->getManageStock();
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param int $qtyRequested
     * @param \Magento\Quote\Model\Quote|null $quote
     *
     * @return float|int
     */
    public function checkAvailableQty(
        \Magento\Catalog\Model\Product $product,
        $qtyRequested,
        $quote = null
    ) {
        $stockItem = $this->stockRegistryProvider->getStockItem(
            $product->getId(),
            $product->getStore()->getWebsiteId()
        );

        $qtyAdded = 0;
        if ($quote instanceof \Magento\Quote\Model\Quote) {
            $items = $quote->getItemsCollection();
        } else {
            $items =  $this->cart->getItems();
        }
        foreach ($items as $item) {
            if ($item->getProductId() == $product->getId()) {
                $qtyAdded += $item->getQty();
            }
        }

        $totalQty = $qtyRequested + $qtyAdded;

        $checkResult = $this->stockStateProvider->checkQuoteItemQty(
            $stockItem,
            $qtyRequested,
            $totalQty,
            $qtyRequested
        );

        if ($checkResult->getData('has_error')) {
            if (!$this->stockStateProvider->checkQty($stockItem, $totalQty)) {
                return $stockItem->getQty() - $qtyAdded;
            }

            if ($stockItem->getBackorders()) {
                return $stockItem->getMaxSaleQty() - $qtyAdded;
            }

            return 0;
        } else {
            return $qtyRequested;
        }
    }
}
