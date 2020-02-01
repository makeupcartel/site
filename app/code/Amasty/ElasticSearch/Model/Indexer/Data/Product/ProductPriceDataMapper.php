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

class ProductPriceDataMapper implements DataMapperInterface
{

    const DEFAULT_PRECISION = 4;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var array
     */
    private $customerGroupIds = [];

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
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $customerGroupCollectionFactory,
        StoreManager $storeManager,
        \Magento\Framework\Stdlib\DateTime $date
    ) {
        $this->resource = $resourceConnection;
        $customerGroupCollection = $customerGroupCollectionFactory->create();
        $this->customerGroupIds = $customerGroupCollection->getAllIds();
        $this->storeManager = $storeManager;
        $this->date = $date;
    }

    /**
     * @param array $documentData
     * @param int $storeId
     * @param array $context
     * @return array
     */
    public function map(array $documentData, $storeId, array $context = [])
    {
        $prices = $this->getProductPriceData(array_keys($documentData), $storeId);
        $priceDocumentData = [];
        foreach ($documentData as $productId => $document) {
            foreach ($this->customerGroupIds as $customerGroupId) {
                $priceValue = isset($prices[$productId][$customerGroupId])
                    ? $prices[$productId][$customerGroupId] : 0;
                $priceDocumentData[$productId]['price_' . $customerGroupId] = $priceValue;
            }
        }

        return $priceDocumentData;
    }

    /**
     * @param array $productIds
     * @param int $storeId
     * @return array
     */
    private function getProductPriceData(array $productIds = [], $storeId = null)
    {
        $result = [];
        if (!empty($productIds)) {
            $connection = $this->resource->getConnection();

            $select = $connection->select()->from(
                $this->resource->getTableName('catalog_product_index_price'),
                ['entity_id', 'customer_group_id', 'min_price']
            );
            $select->where('website_id = ?', $this->storeManager->getStore($storeId)->getWebsiteId());
            if ($productIds) {
                $select->where('entity_id IN (?)', $productIds);
            }

            $result = [];
            foreach ($connection->fetchAll($select) as $row) {
                $result[$row['entity_id']][$row['customer_group_id']] = round(
                    $row['min_price'],
                    self::DEFAULT_PRECISION
                );
            }

            $select = $connection->select()->from(
                $this->resource->getTableName('catalogrule_product_price'),
                ['product_id', 'customer_group_id', 'rule_price']
            );
            $now = new \DateTime();
            $select->where('website_id = ?', $this->storeManager->getStore($storeId)->getWebsiteId())
                ->where('rule_date = ?', $this->date->formatDate($now, false));
            if ($productIds) {
                $select->where('product_id IN (?)', $productIds);
            }
            foreach ($connection->fetchAll($select) as $row) {
                $rulePrice = round($row['rule_price'], self::DEFAULT_PRECISION);
                if (isset($result[$row['product_id']][$row['customer_group_id']])
                    && $result[$row['product_id']][$row['customer_group_id']] > $rulePrice
                ) {
                    $result[$row['product_id']][$row['customer_group_id']] = $rulePrice;
                }
            }
        }

        return $result;
    }
}
