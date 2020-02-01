<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Test\Unit\Model;

use Amasty\Promo\Model\Product;
use Amasty\Promo\Test\Unit\Traits;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class ProductTest
 *
 * @see Product
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class ProductTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const STORE_ID = 1;
    const WEBSITE_ID = 2;

    const STOCK_QTY = 10000;
    const MAX_QTY = 999;

    /**
     * @covers       Product::getProductQty
     *
     * @dataProvider getConstructorArgs
     *
     * @param \Magento\CatalogInventory\Model\StockState $state
     * @param \Magento\CatalogInventory\Model\Configuration $stockConfiguration
     * @param \Magento\CatalogInventory\Model\StockRegistryProvider $stockRegistryProvider
     * @param mixed $expected
     * @param string $productType
     */
    public function testGetProductQty(
        $state,
        $stockConfiguration,
        $stockRegistryProvider,
        $expected,
        $productType = \Magento\Catalog\Model\Product\Type::DEFAULT_TYPE
    ) {
        $model = $this->getObjectManager()->getObject(
            Product::class,
            [
                'state' => $state,
                'productRepository' => $this->getProductRepository(1, $productType),
                'storeManager' => $this->getStoreManager(),
                'stockConfiguration' => $stockConfiguration,
                'stockRegistryProvider' => $stockRegistryProvider
            ]
        );

        $result = $model->getProductQty('SKU');

        $this->assertTrue($result === $expected);
    }

    public function getConstructorArgs()
    {
        $constrArgs = $this->getObjectManager()->getConstructArguments(Product::class);

        return [
            [
                $constrArgs['state'],
                $constrArgs['stockConfiguration'],
                $constrArgs['stockRegistryProvider'],
                false,
                \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
            ],
            [
                $constrArgs['state'],
                $constrArgs['stockConfiguration'],
                $constrArgs['stockRegistryProvider'],
                false,
                \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
            ],
            [
                $constrArgs['state'],
                $this->getStockConfiguration(),
                $this->getStockRegistryProvider(false),
                false,
            ],
            [
                $this->getStockState(),
                $this->getStockConfiguration(),
                $this->getStockRegistryProvider(true, false, 2),
                static::STOCK_QTY,
            ],
            [
                $this->getStockState(),
                $this->getStockConfiguration(),
                $this->getStockRegistryProvider(true, true, 2),
                static::MAX_QTY,
            ],
        ];
    }

    /**
     * @return \Magento\Store\Model\StoreManager|MockObject
     */
    private function getStoreManager()
    {
        /** @var \Magento\Store\Model\Store|MockObject $store */
        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getCode', 'getId', 'getWebsiteId']);
        $store->expects($this->any())->method('getId')->willReturn(self::STORE_ID);
        $store->expects($this->any())->method('getCode')->willReturn('default');
        $store->expects($this->any())->method('getWebsiteId')->willReturn(self::WEBSITE_ID);

        /** @var \Magento\Store\Model\Website|MockObject $website */
        $website = $this->createPartialMock(\Magento\Store\Model\Website::class, ['getId']);
        $website->expects($this->any())->method('getId')->willReturn(static::WEBSITE_ID);

        /** @var \Magento\Store\Model\StoreManager|MockObject $storeManager */
        $storeManager = $this->createMock(\Magento\Store\Model\StoreManager::class);
        $storeManager->expects($this->atLeastOnce())->method('getStore')->willReturn($store);
        $storeManager->expects($this->any())->method('getWebsite')->willReturn($website);

        return $storeManager;
    }

    /**
     * @param int $productId
     * @param string $productType
     *
     * @return MockObject
     */
    private function getProductRepository($productId, $productType)
    {
        $product = $this->objectManager->getObject(\Magento\Catalog\Model\Product::class);
        $product->setId($productId)->setTypeId($productType);

        $productRepository = $this->createMock(\Magento\Catalog\Model\ProductRepository::class);
        $productRepository->expects($this->once())->method('get')->willReturn($product);

        return $productRepository;
    }

    /**
     * @return \Magento\CatalogInventory\Model\Configuration|MockObject
     */
    private function getStockConfiguration()
    {
        /** @var \Magento\CatalogInventory\Model\Configuration|MockObject $stockConfiguration */
        $stockConfiguration = $this->createPartialMock(\Magento\CatalogInventory\Model\Configuration::class, ['getDefaultScopeId']);
        $stockConfiguration->expects($this->once())->method('getDefaultScopeId')->willReturn(true);

        return $stockConfiguration;

    }

    /**
     * @param bool $managingStock
     * @param bool $backorders
     *
     * @param int $exactly
     *
     * @return \Magento\CatalogInventory\Model\StockRegistryProvider|MockObject
     */
    private function getStockRegistryProvider($managingStock = true, $backorders = true, $exactly = 1)
    {
        /** @var \Magento\CatalogInventory\Model\Stock\Item|MockObject $stockItem */
        $stockItem = $this->createPartialMock(
            \Magento\CatalogInventory\Model\Stock\Item::class,
            ['getManageStock', 'getBackorders', 'getMaxSaleQty']
        );
        $stockItem->expects($this->once())->method('getManageStock')->willReturn($managingStock);
        $stockItem->expects($this->once())->method('getBackorders')->willReturn($managingStock && $backorders);
        $stockItem->expects($this->once())->method('getMaxSaleQty')->willReturn(static::MAX_QTY);


        /** @var \Magento\CatalogInventory\Model\StockRegistryProvider|MockObject $stockRegistryProvider */
        $stockRegistryProvider = $this->createPartialMock(
            \Magento\CatalogInventory\Model\StockRegistryProvider::class,
            ['getStockItem']
        );
        $stockRegistryProvider->expects($this->exactly($exactly))->method('getStockItem')->willReturn($stockItem);

        return $stockRegistryProvider;

    }

    /**
     * @return \Magento\CatalogInventory\Model\StockState|MockObject
     */
    private function getStockState()
    {
        /** @var \Magento\CatalogInventory\Model\StockState|MockObject $stockItem */
        $stockState = $this->createPartialMock(\Magento\CatalogInventory\Model\StockState::class, ['getStockQty']);
        $stockState->expects($this->once())->method('getStockQty')->willReturn(static::STOCK_QTY);

        return $stockState;

    }
}
