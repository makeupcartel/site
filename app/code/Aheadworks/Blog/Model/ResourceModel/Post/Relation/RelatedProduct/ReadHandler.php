<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\ResourceModel\Post\Relation\RelatedProduct;

use Magento\Framework\App\ResourceConnection;
use Aheadworks\Blog\Api\Data\PostInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Aheadworks\Blog\Model\ResourceModel\Indexer\ProductPost as ResourceProductPost;

/**
 * Class ReadHandler
 * @package Aheadworks\Blog\Model\ResourceModel\Post\Relation\RelatedProduct
 */
class ReadHandler implements ExtensionInterface
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
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        StoreManagerInterface $storeManager
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        if ($entityId = (int)$entity->getId()) {
            $connection = $this->resourceConnection->getConnectionByName(
                $this->metadataPool->getMetadata(PostInterface::class)->getEntityConnectionName()
            );
            $productLinkField = $this->metadataPool->getMetadata(CategoryInterface::class)->getIdentifierField();
            $productPostTable = $this->resourceConnection->getTableName(ResourceProductPost::BLOG_PRODUCT_POST_TABLE);
            $select = $connection->select()
                ->from($productPostTable, 'product_id')
                ->joinRight(
                    ['product_entity' => $this->resourceConnection->getTableName('catalog_product_entity')],
                    $productPostTable . '.product_id = product_entity.' . $productLinkField,
                    []
                )->where('post_id = :id')
                ->where('store_id = :store_id');
            $relatedProductIds = $connection->fetchCol(
                $select,
                ['id' => $entityId, 'store_id' => $this->storeManager->getStore()->getId()]
            );
            $entity->setRelatedProductIds($relatedProductIds);
        }
        return $entity;
    }
}
