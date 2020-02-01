<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Indexer\Data\Product;

use Magento\Framework\App\ResourceConnection;
use Amasty\ElasticSearch\Api\Data\Indexer\Data\DataMapperInterface;

class ProductCategoryDataMapper implements DataMapperInterface
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resource = $resourceConnection;
    }

    /**
     * @param array $documentData
     * @param int $storeId
     * @param array $context
     * @return array
     */
    public function map(array $documentData, $storeId, array $context = [])
    {
        $categoryIds = $this->getProductCategoryData($storeId, array_keys($documentData));
        $categoryDocumentData = [];
        foreach ($documentData as $productId => $document) {
            $categoryDocumentData[$productId]['category_ids'] =
                isset($categoryIds[$productId]) ? $categoryIds[$productId] : [];
        }

        return $categoryDocumentData;
    }

    /**
     * @param int $storeId
     * @param array $productIds
     * @return array
     */
    private function getProductCategoryData($storeId, array $productIds = [])
    {
        $result = [];
        if (!empty($productIds)) {
            $connection = $this->resource->getConnection();
            $tableName = $this->resource->getTableName('catalog_category_product_index_store' . $storeId);
            $tableName = $connection->isTableExists($tableName)
                ? $tableName : $this->resource->getTableName('catalog_category_product_index');
            $select = $connection->select()->from(
                [$tableName],
                ['category_id', 'product_id']
            )->where('product_id IN (?)', $productIds);

            foreach ($connection->fetchAll($select) as $row) {
                $result[$row['product_id']][] = $row['category_id'];
            }
        }

        return $result;
    }
}
