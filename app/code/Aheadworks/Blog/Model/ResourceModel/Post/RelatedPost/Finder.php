<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\ResourceModel\Post\RelatedPost;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Aheadworks\Blog\Model\Source\Post\CustomerGroups;
use Aheadworks\Blog\Model\Source\Post\Status as PostStatus;
use Magento\Store\Model\Store;
use Aheadworks\Blog\Model\Config;

/**
 * Class Finder
 *
 * @package Aheadworks\Blog\Model\ResourceModel\Post\RelatedPost
 */
class Finder
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var string
     */
    private $postTable;

    /**
     * @param ResourceConnection $resource
     * @param Config $config
     */
    public function __construct(
        ResourceConnection $resource,
        Config $config
    ) {
        $this->resource = $resource;
        $this->config = $config;
        $this->postTable = $this->resource->getTableName('aw_blog_post');
    }

    /**
     * Find related posts from provided post ID
     *
     * @param int $postId
     * @param int $storeId
     * @param int $customerGroupId
     * @return array
     * @throws \Zend_Db_Select_Exception
     */
    public function find($postId, $storeId, $customerGroupId)
    {
        $numberToDisplay = $this->config->getQtyOfRelatedPosts();
        if (!$numberToDisplay) {
            return [];
        }
        $postStoreTable = $this->resource->getTableName('aw_blog_post_store');
        $conn = $this->getConnection();

        $selects = [
            $conn->select()->from(['a' => $this->getSelectByTags($postId)]),
            $conn->select()->from(['b' => $this->getSelectByCategory($postId)]),
            $conn->select()->from(['c' => $this->getSelectForAllPostsOrderedByPublishedDate($postId)]),
        ];
        $unionSelect = $conn->select()->union($selects);

        $customerGroupCondition = [
            $conn->quoteInto('FIND_IN_SET(?, post_table.customer_groups)', CustomerGroups::ALL_GROUPS),
            $conn->quoteInto('FIND_IN_SET(?, post_table.customer_groups)', $customerGroupId),
        ];
        $storeCondition = [
            $conn->quoteInto('post_store_table.store_id = ?', Store::DEFAULT_STORE_ID),
            $conn->quoteInto('post_store_table.store_id = ?', $storeId),
        ];

        $completeSelect = $conn->select()
            ->from(['result_table' => $unionSelect])
            ->joinLeft(
                ['post_table' => $this->postTable],
                'post_table.id = result_table.post_id',
                []
            )->joinLeft(
                ['post_store_table' => $postStoreTable],
                'post_store_table.post_id = result_table.post_id',
                []
            )->where(implode(' OR ', $customerGroupCondition))
            ->where(implode(' OR ', $storeCondition))
            ->where('post_table.status = ?', PostStatus::PUBLICATION)
            ->group('result_table.post_id')
            ->order('result_table.tag_count DESC')
            ->order('result_table.publish_date DESC')
            ->limit($numberToDisplay);

        return $conn->fetchCol($completeSelect);
    }

    /**
     * Get select for related posts by tags relevance
     *
     * @param int $postId
     * @return Select
     */
    public function getSelectByTags($postId)
    {
        $postTagTable = $this->resource->getTableName('aw_blog_post_tag');
        $connection = $this->getConnection();

        $currentPostTagsSubSelect = $connection->select()
            ->from($postTagTable, ['tag_id'])
            ->where('post_id = ?', $postId);

        $condition = [
            $connection->quoteInto('tag_id IN ?', $currentPostTagsSubSelect),
            $connection->quoteInto('post_id != ?', $postId),
        ];
        $select = $connection->select()
            ->from(
                ['post_tag_table' => $postTagTable],
                ['post_id', new \Zend_Db_Expr('count(*) AS tag_count')]
            )->joinLeft(
                ['post_table' => $this->postTable],
                'post_table.id = post_tag_table.post_id',
                ['publish_date']
            )->where(implode(' AND ', $condition))
            ->group('post_id')
            ->order('tag_count DESC')
            ->order('post_table.publish_date DESC');

        return $select;
    }

    /**
     * Get select for related posts by the same category the specified post is found in
     *
     * @param int $postId
     * @return Select
     */
    public function getSelectByCategory($postId)
    {
        $postCategoryTable = $this->resource->getTableName('aw_blog_post_category');
        $connection = $this->getConnection();

        $currentPostCategoriesSubSelect = $connection->select()
            ->from($postCategoryTable, ['category_id'])
            ->where('post_id = ?', $postId);

        $condition = [
            $connection->quoteInto('category_id IN ?', $currentPostCategoriesSubSelect),
            $connection->quoteInto('post_id != ?', $postId),
        ];
        $select = $connection->select()
            ->from(
                ['post_table' => $this->postTable],
                [
                    new \Zend_Db_Expr('id AS post_id'),
                    new \Zend_Db_Expr('0 AS tag_count'),
                    'publish_date'
                ]
            )->joinRight(
                ['post_cat_table' => $postCategoryTable],
                'post_cat_table.post_id = post_table.id',
                []
            )->where(implode(' AND ', $condition))
            ->group('post_id')
            ->order('post_table.publish_date DESC');

        return $select;
    }

    /**
     * Get select for all posts ordered by published date
     *
     * @param int $postId
     * @return Select
     */
    public function getSelectForAllPostsOrderedByPublishedDate($postId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()
            ->from(
                ['post_table' => $this->postTable],
                [
                    new \Zend_Db_Expr('id AS post_id'),
                    new \Zend_Db_Expr('-1 AS tag_count'),
                    'publish_date'
                ]
            )->where('id != ?', $postId)
            ->order('post_table.publish_date DESC');

        return $select;
    }

    /**
     * Get connection
     *
     * @return AdapterInterface
     */
    private function getConnection()
    {
        if (!isset($this->connection)) {
            $this->connection = $this->resource->getConnection();
        }
        return $this->connection;
    }
}
