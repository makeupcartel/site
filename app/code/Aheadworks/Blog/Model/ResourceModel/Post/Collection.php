<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\ResourceModel\Post;

use Aheadworks\Blog\Api\Data\AuthorInterface;
use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\Model\Post;
use Aheadworks\Blog\Model\ResourceModel\Post as ResourcePost;
use Aheadworks\Blog\Model\Source\Post\CustomerGroups;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;
use Magento\Framework\DB\Select;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Aheadworks\Blog\Model\ResourceModel\Indexer\ProductPost as ResourceProductPost;
use Aheadworks\Blog\Model\ResourceModel\Tag as ResourceTag;
use Aheadworks\Blog\Model\ResourceModel\Author as ResourceAuthor;
use Aheadworks\Blog\Model\ResourceModel\Category as ResourceCategory;
use Aheadworks\Blog\Model\ResourceModel\AbstractCollection;

/**
 * Class Collection
 * @package Aheadworks\Blog\Model\ResourceModel\Post
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    const IS_NEED_TO_ATTACH_RELATED_PRODUCT_IDS = 'is_need_to_attach_related_product_ids';

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = 'id';

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param EventManager $eventManager
     * @param DateTime $dateTime
     * @param MetadataPool $metadataPool
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        EventManager $eventManager,
        DateTime $dateTime,
        MetadataPool $metadataPool,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
        $this->dateTime = $dateTime;
        $this->metadataPool = $metadataPool;

        $this->setFlag(self::IS_NEED_TO_ATTACH_RELATED_PRODUCT_IDS, true);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(Post::class, ResourcePost::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        $this->attachStores(ResourcePost::BLOG_POST_STORE_TABLE, 'id', 'post_id');
        $this->attachCategories();
        $this->attachTagNames();
        $this->attachAuthor();
        if ($this->getFlag(self::IS_NEED_TO_ATTACH_RELATED_PRODUCT_IDS)) {
            $this->attachRelatedProductIds();
        }
        return parent::_afterLoad();
    }

    /**
     *  Add category filter
     *
     * @param int|array $categories
     * @return $this
     */
    public function addCategoryFilter($categories)
    {
        if (!is_array($categories)) {
            $categories = [$categories];
        }
        $select = $this->getConnection()
            ->select()
            ->from($this->getTable(ResourceCategory::BLOG_CATEGORY_TABLE), ['id']);
        foreach ($categories as $category) {
            $select->orWhere("`path` LIKE '" . $category . "/%' OR `path` LIKE '%/" . $category . "/%'");
        }
        $categoryIds = array_merge($categories, (array)$this->getConnection()->fetchCol($select));

        $this->addFilter('category_id', ['in' => $categoryIds], 'public');
        return $this;
    }

    /**
     * Add tag filter
     *
     * @param int|array $tag
     * @return $this
     */
    public function addTagFilter($tag)
    {
        if (!is_array($tag)) {
            $tag = [$tag];
        }
        $this->addFilter('tag_id', ['in' => $tag], 'public');
        return $this;
    }

    /**
     * Add related product filter
     *
     * @param int|array $product
     * @return $this
     */
    public function addRelatedProductFilter($product)
    {
        if (!is_array($product)) {
            $product = [$product];
        }
        $this->addFilter('product_id', ['in' => $product], 'public');
        return $this;
    }

    /**
     * Add customer groups filter. First of all it checks 'all groups'
     *    option and then specified one.
     *
     * @param string $customerGroup
     * @return $this
     */
    public function addCustomerGroupFilter($customerGroup)
    {
        $condition = [
            ['finset' => CustomerGroups::ALL_GROUPS],
            ['finset' => $customerGroup],
        ];
        $this->addFilter('customer_groups', $condition, 'public');
        return $this;
    }

    /**
     * Retrieve current loaded post ids considering limit and offset
     *
     * @return array
     */
    public function getCurrentLoadedIds()
    {
        $connection = $this->getConnection();
        $countQuery = $connection->select()->from($this->getSelect(), PostInterface::ID);
        return $connection->fetchCol($countQuery);
    }

    /**
     * {@inheritdoc}
     */
    protected function _renderFiltersBefore()
    {
        $this->joinStoreLinkageTable(ResourcePost::BLOG_POST_STORE_TABLE, 'id', 'post_id');
        $this->joinCategoryLinkageTable();
        $this->joinTagLinkageTable();
        $this->joinRelatedProductLinkageTable();
        parent::_renderFiltersBefore();
    }

    /**
     * Join to category linkage table if category filter is applied
     *
     * @return void
     */
    private function joinCategoryLinkageTable()
    {
        if ($this->getFilter('category_id')) {
            $select = $this->getSelect();
            $select->joinLeft(
                ['category_linkage_table' => $this->getTable(ResourcePost::BLOG_POST_CATEGORY_TABLE)],
                'main_table.id = category_linkage_table.post_id',
                []
            )
            ->group('main_table.id');
        }
    }

    /**
     * Join to tag linkage table if tag filter is applied
     *
     * @return void
     */
    private function joinTagLinkageTable()
    {
        if ($this->getFilter('tag_id')) {
            $select = $this->getSelect();
            $select->joinLeft(
                ['tag_linkage_table' => $this->getTable(ResourcePost::BLOG_POST_TAG_TABLE)],
                'main_table.id = tag_linkage_table.post_id',
                []
            )
            ->group('main_table.id');
        }
    }

    /**
     * Join to product index linkage table if product filter is applied
     *
     * @return void
     */
    private function joinRelatedProductLinkageTable()
    {
        if ($this->getFilter('product_id')) {
            $select = $this->getSelect();
            $select->joinLeft(
                ['product_post_linkage_table' => $this->getTable(ResourceProductPost::BLOG_PRODUCT_POST_TABLE)],
                'main_table.id = product_post_linkage_table.post_id',
                []
            )
            ->group('main_table.id');
            if ($storeFilter = $this->getFilter('store_linkage_table.store_id')) {
                $select->where(
                    $this->_getConditionSql('product_post_linkage_table.store_id', $storeFilter->getValue()),
                    null,
                    Select::TYPE_CONDITION
                );
            }
        }
    }

    /**
     * Attach categories data to collection items
     *
     * @return void
     */
    private function attachCategories()
    {
        $postIds = $this->getColumnValues('id');
        if (count($postIds)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from(['category_linkage_table' => $this->getTable(ResourcePost::BLOG_POST_CATEGORY_TABLE)])
                ->where('category_linkage_table.post_id IN (?)', $postIds);
            $result = $connection->fetchAll($select);
            /** @var \Magento\Framework\DataObject $item */
            foreach ($this as $item) {
                $categoryIds = [];
                $postId = $item->getData('id');
                foreach ($result as $data) {
                    if ($data['post_id'] == $postId) {
                        $categoryIds[] = $data['category_id'];
                    }
                }
                $item->setData('category_ids', $categoryIds);
            }
        }
    }

    /**
     * Attach tag names to collection items
     *
     * @return void
     */
    private function attachTagNames()
    {
        $postIds = $this->getColumnValues('id');
        if (count($postIds)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from(['tags_table' => $this->getTable(ResourceTag::BLOG_TAG_TABLE)])
                ->joinLeft(
                    ['tag_post_linkage_table' => $this->getTable(ResourcePost::BLOG_POST_TAG_TABLE)],
                    'tags_table.id = tag_post_linkage_table.tag_id',
                    ['post_id' => 'tag_post_linkage_table.post_id']
                )
                ->where('tag_post_linkage_table.post_id IN (?)', $postIds);
            /** @var \Magento\Framework\DataObject $item */
            $result = $connection->fetchAll($select);
            foreach ($this as $item) {
                $tagNames = [];
                $postId = $item->getData('id');
                foreach ($result as $data) {
                    if ($data['post_id'] == $postId) {
                        $tagNames[] = $data['name'];
                    }
                }
                $item->setData('tag_names', $tagNames);
            }
        }
    }

    /**
     * Attach product ids data to collection items
     *
     * @return void
     */
    private function attachRelatedProductIds()
    {
        $postIds = $this->getColumnValues('id');
        if (count($postIds)) {
            $productLinkField = $this->metadataPool->getMetadata(CategoryInterface::class)->getLinkField();
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from(['product_post_linkage_table' => $this->getTable(ResourceProductPost::BLOG_PRODUCT_POST_TABLE)])
                ->joinRight(
                    ['product_entity' => $this->getTable('catalog_product_entity')],
                    'product_post_linkage_table.product_id = product_entity.' . $productLinkField,
                    []
                )->where('product_post_linkage_table.post_id IN (?)', $postIds);
            if ($storeFilter = $this->getFilter('store_linkage_table.store_id')) {
                $select->where(
                    $this->_getConditionSql('product_post_linkage_table.store_id', $storeFilter->getValue()),
                    null,
                    Select::TYPE_CONDITION
                );
            }
            /** @var \Magento\Framework\DataObject $item */
            $result = $connection->fetchAll($select);
            foreach ($this as $item) {
                $productIds = [];
                $postId = $item->getData('id');
                foreach ($result as $data) {
                    if ($data['post_id'] == $postId) {
                        $productIds[] = $data['product_id'];
                    }
                }
                $item->setData('related_product_ids', $productIds);
            }
        }
    }

    /**
     * Attach author to collection items
     *
     * @return void
     */
    private function attachAuthor()
    {
        $authorIds = $this->getColumnValues(PostInterface::AUTHOR_ID);
        if (count($authorIds)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from(
                    ['author_table' => $this->getTable(ResourceAuthor::BLOG_AUTHOR_TABLE)],
                    [
                        AuthorInterface::ID,
                        AuthorInterface::FIRSTNAME,
                        AuthorInterface::LASTNAME,
                        AuthorInterface::URL_KEY,
                        AuthorInterface::IMAGE_FILE,
                        AuthorInterface::SHORT_BIO,
                        AuthorInterface::TWITTER_ID
                    ]
                )
                ->where('author_table.id IN (?)', $authorIds);
            $result = $connection->fetchAll($select);

            /** @var Post $item */
            foreach ($this as $item) {
                $authorId = $item->getData(PostInterface::AUTHOR_ID);
                $authorData = [];
                foreach ($result as $data) {
                    if ($data[PostInterface::ID] == $authorId) {
                        $authorData = $data;
                        break;
                    }
                }
                $item->setData(PostInterface::AUTHOR, $authorData);
            }
        }
    }
}
