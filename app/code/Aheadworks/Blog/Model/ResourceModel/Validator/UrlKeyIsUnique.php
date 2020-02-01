<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\ResourceModel\Validator;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\TypeResolver;
use Aheadworks\Blog\Model\ResourceModel\Category as ResourceCategory;
use Aheadworks\Blog\Model\ResourceModel\Post as ResourcePost;
use Aheadworks\Blog\Model\ResourceModel\Author as ResourceAuthor;

/**
 * Class UrlKeyIsUnique
 * @package Aheadworks\Blog\Model\ResourceModel\Validator
 */
class UrlKeyIsUnique
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var TypeResolver
     */
    private $typeResolver;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param MetadataPool $metadataPool
     * @param TypeResolver $typeResolver
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        MetadataPool $metadataPool,
        TypeResolver $typeResolver,
        ResourceConnection $resourceConnection
    ) {
        $this->metadataPool = $metadataPool;
        $this->typeResolver = $typeResolver;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Checks whether the URL-Key is unique
     *
     * @param object $entity
     * @return bool
     * @throws \Exception
     */
    public function validate($entity)
    {
        $entityType = $this->typeResolver->resolve($entity);
        $metaData = $this->metadataPool->getMetadata($entityType);
        $connection = $this->resourceConnection
            ->getConnectionByName($metaData->getEntityConnectionName());

        $checkTables = [
            $this->resourceConnection->getTableName(ResourcePost::BLOG_POST_TABLE),
            $this->resourceConnection->getTableName(ResourceCategory::BLOG_CATEGORY_TABLE),
            $this->resourceConnection->getTableName(ResourceAuthor::BLOG_AUTHOR_TABLE)
        ];
        foreach ($checkTables as $table) {
            $select = $connection->select()
                ->from($table)
                ->where('url_key = :url_key');
            $bind = ['url_key' => $entity->getUrlKey()];
            if ($entity->getId() && $table == $metaData->getEntityTable()) {
                $select->where($metaData->getIdentifierField() . ' <> :id');
                $bind['id'] = $entity->getId();
            }
            if ($connection->fetchRow($select, $bind)) {
                return false;
            }
        }
        return true;
    }
}
