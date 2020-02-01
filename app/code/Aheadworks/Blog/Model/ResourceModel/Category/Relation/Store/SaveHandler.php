<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\ResourceModel\Category\Relation\Store;

use Magento\Framework\App\ResourceConnection;
use Aheadworks\Blog\Api\Data\CategoryInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Aheadworks\Blog\Model\ResourceModel\Category as ResourceCategory;

/**
 * Class SaveHandler
 * @package Aheadworks\Blog\Model\ResourceModel\Category\Relation\Store
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(MetadataPool $metadataPool, ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $entityId = (int)$entity->getId();
        $storeIds = $entity->getStoreIds();
        $storeIdsOrig = $this->getStoreIds($entityId);

        $toInsert = array_diff($storeIds, $storeIdsOrig);
        $toDelete = array_diff($storeIdsOrig, $storeIds);

        $connection = $this->getConnection();
        $tableName = $this->resourceConnection->getTableName(ResourceCategory::BLOG_CATEGORY_STORE_TABLE);

        if ($toInsert) {
            $data = [];
            foreach ($toInsert as $storeId) {
                $data[] = [
                    'category_id' => (int)$entityId,
                    'store_id' => (int)$storeId,
                ];
            }
            $connection->insertMultiple($tableName, $data);
        }
        if (count($toDelete)) {
            $connection->delete(
                $tableName,
                ['category_id = ?' => $entityId, 'store_id IN (?)' => $toDelete]
            );
        }
        return $entity;
    }

    /**
     * Get store IDs to which entity is assigned
     *
     * @param int $entityId
     * @return array
     */
    private function getStoreIds($entityId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->resourceConnection->getTableName(ResourceCategory::BLOG_CATEGORY_STORE_TABLE), 'store_id')
            ->where('category_id = :id');
        return $connection->fetchCol($select, ['id' => $entityId]);
    }

    /**
     * Get connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @throws \Exception
     */
    private function getConnection()
    {
        return $this->resourceConnection->getConnectionByName(
            $this->metadataPool->getMetadata(CategoryInterface::class)->getEntityConnectionName()
        );
    }
}
