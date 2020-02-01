<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2019-07-16T12:58:54+00:00
 * File:          app/code/Xtento/ProductExport/Model/Export/Data/Product/Stock.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model\Export\Data\Product;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;

class Stock extends \Xtento\ProductExport\Model\Export\Data\AbstractData
{
    protected static $stockIdCache = [];

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Stock constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Xtento\XtCore\Helper\Date $dateHelper
     * @param \Xtento\XtCore\Helper\Utils $utilsHelper
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Xtento\XtCore\Helper\Date $dateHelper,
        \Xtento\XtCore\Helper\Utils $utilsHelper,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        ObjectManagerInterface $objectManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $dateHelper, $utilsHelper, $resource, $resourceCollection, $data);
        $this->stockRegistry = $stockRegistry;
        $this->resourceConnection = $resourceConnection;
        $this->objectManager = $objectManager;
    }

    public function getConfiguration()
    {
        return [
            'name' => 'Stock information',
            'category' => 'Product',
            'description' => 'Export stock information such as qty on stock.',
            'enabled' => true,
            'apply_to' => [\Xtento\ProductExport\Model\Export::ENTITY_PRODUCT],
        ];
    }

    // @codingStandardsIgnoreStart
    public function getExportData($entityType, $collectionItem)
    {
        // @codingStandardsIgnoreEnd
        // Set return array
        $returnArray = [];
        $this->writeArray = & $returnArray; // Write directly on product level
        // Fetch fields to export
        $product = $collectionItem->getProduct();

        $exportAllFields = false;
        if ($this->getProfile()->getOutputType() == 'xml') {
            $exportAllFields = true;
        }

        if ($this->fieldLoadingRequired('stock') && !$exportAllFields) {
            $returnArray['stock'] = [];
            $this->writeArray = & $returnArray['stock'];

            $stockItem = $this->stockRegistry->getStockItem($product->getId(), $this->getStoreId());
            if ($stockItem->getId()) {
                foreach ($stockItem->getData() as $key => $value) {
                    if (!$this->fieldLoadingRequired($key)) {
                        continue;
                    }
                    if ($key == 'qty') {
                        $value = sprintf('%d', $value);
                    }
                    $this->writeValue($key, $value);
                }
            }

            $this->writeArray = & $returnArray; // Write on product level
        }

        // 2.3 MSI sources
        if (version_compare($this->utilsHelper->getMagentoVersion(), '2.3', '>=')
            && $this->utilsHelper->isExtensionInstalled('Magento_Inventory')
        ) {
            if ($this->fieldLoadingRequired('msi_stocks')) {
                $returnArray['msi_stocks'] = [];
                try {
                    $stockInfo = $this->objectManager->get('Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku')->execute($product->getSku());
                    foreach ($stockInfo as $stockSource) {
                        $this->writeArray = &$returnArray['msi_stocks'][];
                        foreach ($stockSource as $key => $value) {
                            $this->writeValue($key, $value);
                        }
                    }
                } catch (NoSuchEntityException $e) {} catch (InputException $e) {}
                $this->writeArray = &$returnArray; // Write on product level
            }
            if ($this->fieldLoadingRequired('msi_sources')) {
                $returnArray['msi_sources'] = [];
                try {
                    $stockInfo = $this->objectManager->get('Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku')->execute($product->getSku());
                    foreach ($stockInfo as $stockSource) {
                        $this->writeArray = &$returnArray['msi_sources'][];
                        foreach ($stockSource as $key => $value) {
                            $this->writeValue($key, $value);
                        }
                    }
                } catch (NoSuchEntityException $e) {} catch (InputException $e) {}
                $this->writeArray = &$returnArray; // Write on product level
            }
        }

        // Fetch stock for different stock_ids
        if (($this->fieldLoadingRequired('stock_ids') || $this->fieldLoadingRequired('total_stock')) && !$exportAllFields) {
            if (!isset(self::$stockIdCache[$product->getId()])) {
                $select = $this->resourceConnection->getConnection()->select()
                    ->from($this->resourceConnection->getTableName('cataloginventory_stock_item'), ['product_id', 'stock_id', 'qty']
                    )
                    ->where('product_id = ?', $product->getId());
                $stockItems = $this->resourceConnection->getConnection()->fetchAll($select);

                foreach ($stockItems as $stockItem) {
                    self::$stockIdCache[$stockItem['product_id']][$stockItem['stock_id']] = $stockItem['qty'];
                }
            }
            $totalStockQty = 0;
            $returnArray['stock_ids'] = [];
            if (isset(self::$stockIdCache[$product->getId()])) {
                foreach (self::$stockIdCache[$product->getId()] as $stockId => $qty) {
                    if ($stockId > 0) {
                        $this->writeArray = & $returnArray['stock_ids'][];
                        $this->writeValue('stock_id', $stockId);
                        $this->writeValue('qty', $qty);
                        $totalStockQty += $qty;
                    }
                }
            }
            $this->writeArray = & $returnArray; // Write on product level
            $this->writeValue('total_stock_qty', $totalStockQty);
        }

        // Done
        return $returnArray;
    }
}