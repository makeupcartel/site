<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\ResourceModel\Indexer;

use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Indexer\Table\StrategyInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Indexer\Model\ResourceModel\AbstractResource;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Catalog\Model\Product;
use Aheadworks\Blog\Model\Post;
use Aheadworks\Blog\Model\ResourceModel\Indexer\ProductPost\DataCollector;
use Aheadworks\Blog\Model\ResourceModel\Indexer\ProductPost\DataProcessorFactory;
use Aheadworks\Blog\Model\Indexer\MultiThread\PostDimension;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Blog\Model\ResourceModel\Indexer\ProductPost\DataProcessor\DataProcessorInterface;

/**
 * Class ProductPost
 *
 * @package Aheadworks\Blog\Model\ResourceModel\Indexer
 */
class ProductPost extends AbstractResource implements IdentityInterface
{
    /**#@+
     * Constants defined for tables
     */
    const BLOG_PRODUCT_POST_TABLE = 'aw_blog_product_post';
    const BLOG_PRODUCT_POST_INDEX_TABLE = 'aw_blog_product_post_idx';
    const BLOG_PRODUCT_POST_TMP_TABLE = 'aw_blog_product_post_tmp';
    /**#@-*/

    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * @var DataCollector
     */
    private $dataCollector;

    /**
     * @var DataProcessorInterface
     */
    private $dataProcessor;

    /**
     * @var array
     */
    private $entities = [];

    /**
     * @param Context $context
     * @param StrategyInterface $tableStrategy
     * @param EventManagerInterface $eventManager
     * @param DataCollector $dataCollector
     * @param DataProcessorFactory $dataProcessorFactory
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        StrategyInterface $tableStrategy,
        EventManagerInterface $eventManager,
        DataCollector $dataCollector,
        DataProcessorFactory $dataProcessorFactory,
        $connectionName = null
    ) {
        parent::__construct($context, $tableStrategy, $connectionName);
        $this->eventManager = $eventManager;
        $this->dataCollector = $dataCollector;
        $this->dataProcessor = $dataProcessorFactory->create();
    }

    /**
     * Define main product post index table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::BLOG_PRODUCT_POST_TABLE, 'product_id');
    }

    /**
     * Reindex all product post data
     *
     * @return $this
     * @throws \Exception
     */
    public function reindexAll()
    {
        $this->tableStrategy->setUseIdxTable(true);
        $this->clearTemporaryIndexTable();
        $this->beginTransaction();
        try {
            $toInsert = $this->dataCollector->prepareProductPostData();
            $this->dataProcessor->insertDataToTable($toInsert, $this->getIdxTable());
            $this->commit();
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
        $this->syncData();
        $this->dispatchCleanCacheByTags($toInsert);
        return $this;
    }

    /**
     * Reindex product post data for defined ids
     *
     * @param array|int $ids
     * @return $this
     * @throws LocalizedException
     */
    public function reindexRows($ids)
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $dataToUpdate = $this->dataCollector->prepareProductPostData($ids);
        $this->beginTransaction();
        try {
            $this->getConnection()->delete(
                $this->getMainTable(),
                ['post_id IN (?)' => $ids]
            );
            $this->dataProcessor->insertDataToTable($dataToUpdate, $this->getMainTable());
            $this->commit();
            $this->dispatchCleanCacheByTags($dataToUpdate);
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * Reindex product post data for dimension
     *
     * @param PostDimension $dimension
     * @param string $tableName
     * @return $this
     * @throws \Exception
     */
    public function reindexDimension($dimension, $tableName)
    {
        try {
            $dataToInsert = $this->dataCollector->prepareProductPostData($dimension->getValue());
            $this->dataProcessor->insertDataToTable($dataToInsert, $tableName);
            $this->dispatchCleanCacheByTags($dataToInsert);
        } catch (\Exception $e) {
            throw $e;
        }
        return $this;
    }

    /**
     * Dispatch clean_cache_by_tags event
     *
     * @param array $entities
     * @return void
     */
    private function dispatchCleanCacheByTags($entities = [])
    {
        $this->entities = $entities;
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this]);
    }

    /**
     * @inheritdoc
     */
    public function getIdentities()
    {
        $identities = [Product::CACHE_TAG];
        foreach ($this->entities as $entity) {
            $postTag = Post::CACHE_TAG . '_' . $entity['post_id'];
            if (false === array_search($postTag, $identities)) {
                $identities[] = $postTag;
            }
        }
        return $identities;
    }
}
