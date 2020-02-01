<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Indexer\Data\Product;

use Magento\Framework\App\ResourceConnection;
use Amasty\ElasticSearch\Api\Data\Indexer\Data\DataMapperInterface;
use Magento\Store\Model\StoreManager;
use Amasty\ElasticSearch\Model\Indexer\Structure\EntityBuilder\Product\StockStatus;

class ProductStockStatusDataMapper implements DataMapperInterface
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $date;


    public function __construct(
        ResourceConnection $resourceConnection,
        StoreManager $storeManager
    ) {
        $this->resource = $resourceConnection;
        $this->storeManager = $storeManager;
    }

    /**
     * @param array $documentData
     * @param int $storeId
     * @param array $context
     * @return array
     */
    public function map(array $documentData, $storeId, array $context = [])
    {
        $statusData = $this->getProductStockStatusData(array_keys($documentData));
        $stockStatusDocumentData = [];
        foreach ($documentData as $productId => $document) {
            $stockStatus = isset($statusData[$productId]) ? $statusData[$productId] : 0;
            $stockStatusDocumentData[$productId][StockStatus::STOCK_STATUS] = $stockStatus;
        }

        return $stockStatusDocumentData;
    }

    /**
     * @param array $productIds
     * @return array
     */
    private function getProductStockStatusData(array $productIds = [])
    {
        $result = [];
        if (!empty($productIds)) {
            $connection = $this->resource->getConnection();

            $select = $connection->select()->from(
                $this->resource->getTableName('cataloginventory_stock_status'),
                ['product_id', 'stock_status']
            );
            if ($productIds) {
                $select->where('product_id IN (?)', $productIds);
            }

            $result = [];
            foreach ($connection->fetchAll($select) as $row) {
                $result[$row['product_id']] = (int)$row['stock_status'];
            }
        }

        return $result;
    }
}
