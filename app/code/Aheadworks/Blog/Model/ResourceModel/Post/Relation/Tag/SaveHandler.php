<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\ResourceModel\Post\Relation\Tag;

use Magento\Framework\App\ResourceConnection;
use Aheadworks\Blog\Api\Data\PostInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Aheadworks\Blog\Model\ResourceModel\Tag as ResourceTag;
use Aheadworks\Blog\Model\ResourceModel\Post as ResourcePost;

/**
 * Class SaveHandler
 * @package Aheadworks\Blog\Model\ResourceModel\Post\Relation\Tag
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var \Aheadworks\Blog\Model\TagFactory
     */
    private $tagFactory;

    /**
     * @param EntityManager $entityManager
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param \Aheadworks\Blog\Model\TagFactory $tagFactory
     */
    public function __construct(
        EntityManager $entityManager,
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        \Aheadworks\Blog\Model\TagFactory $tagFactory
    ) {
        $this->entityManager = $entityManager;
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->tagFactory = $tagFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $entityId = (int)$entity->getId();
        $tagNames = $entity->getTagNames() ? : [];

        $entityTags = [];
        $existingTags = [];
        foreach ($this->getTagsData($tagNames, $entityId) as $data) {
            $tagName = $data['name'];
            if ($data['post_id'] == $entityId) {
                $entityTags[$data['id']] = $tagName;
            } else {
                $existingTags[$data['id']] = $tagName;
            }
        }

        $new = array_udiff($tagNames, $entityTags, 'strcasecmp');
        $toCreate = array_udiff($new, $existingTags, 'strcasecmp');
        $toInsert = array_udiff($new, $toCreate, 'strcasecmp');
        $toDelete = array_udiff($entityTags, $tagNames, 'strcasecmp');

        if ($toInsert) {
            $this->saveRelations(
                $entityId,
                array_keys(array_uintersect($existingTags, $toInsert, 'strcasecmp'))
            );
        }
        if ($toCreate) {
            $this->saveRelations($entityId, $this->createTags($toCreate));
        }
        if ($toDelete) {
            $this->getConnection()->delete(
                $this->resourceConnection->getTableName(ResourcePost::BLOG_POST_TAG_TABLE),
                [
                    'post_id = ?' => $entityId,
                    'tag_id IN (?)' => array_keys(array_uintersect($entityTags, $toDelete, 'strcasecmp'))
                ]
            );
        }

        return $entity;
    }

    /**
     * Get tags data with given tag names or associated to a given entity Id
     *
     * @param array $tagNames
     * @param int $entityId
     * @return array
     */
    public function getTagsData(array $tagNames, $entityId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(['tag' => $this->resourceConnection->getTableName(ResourceTag::BLOG_TAG_TABLE)], ['id', 'name'])
            ->joinLeft(
                ['tag_post' => $this->resourceConnection->getTableName(ResourcePost::BLOG_POST_TAG_TABLE)],
                'tag.id = tag_post.tag_id',
                ['post_id']
            )
            ->where('name IN(?)', $tagNames)
            ->orWhere('post_id = ?', $entityId);
        return $connection->fetchAll($select);
    }

    /**
     * Create tags, return IDs of created tags
     *
     * @param array $tagNames
     * @return int[]
     */
    private function createTags(array $tagNames)
    {
        $tagIds = [];
        foreach ($tagNames as $tagName) {
            /** @var \Aheadworks\Blog\Model\Tag $tag */
            $tag = $this->tagFactory->create();
            $tag->setName($tagName);
            $this->entityManager->save($tag);
            $tagIds[] = $tag->getId();
        }
        return $tagIds;
    }

    /**
     * Insert rows with tag IDs into tag relation table
     *
     * @param int $entityId
     * @param array $tagIds
     * @return void
     */
    private function saveRelations($entityId, array $tagIds)
    {
        $data = [];
        foreach ($tagIds as $tagId) {
            $data[] = [
                'tag_id' => $tagId,
                'post_id' => $entityId
            ];
        }
        $this->getConnection()->insertMultiple(
            $this->resourceConnection->getTableName(ResourcePost::BLOG_POST_TAG_TABLE),
            $data
        );
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
            $this->metadataPool->getMetadata(PostInterface::class)->getEntityConnectionName()
        );
    }
}
