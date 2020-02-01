<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\ResourceModel\TagCloudItem;

use Aheadworks\Blog\Api\Data\TagCloudItemInterface;
use Aheadworks\Blog\Model\Source\Post\Status as PostStatus;
use Aheadworks\Blog\Model\Tag;
use Aheadworks\Blog\Model\ResourceModel\Tag as ResourceTag;
use Magento\Store\Model\Store;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;
use Aheadworks\Blog\Model\ResourceModel\Post as ResourcePost;

/**
 * Class Collection
 * @package Aheadworks\Blog\Model\ResourceModel\TagCloudItem
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param EventManager $eventManager
     * @param DateTime $dateTime
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        EventManager $eventManager,
        DateTime $dateTime,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->dateTime = $dateTime;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(Tag::class, ResourceTag::class);
        $this->_map['fields']['category_id'] = 'post_category_table.category_id';
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()
            ->joinLeft(
                ['post_tag_table' => $this->getTable(ResourcePost::BLOG_POST_TAG_TABLE)],
                'main_table.id = post_tag_table.tag_id',
                []
            )
            ->joinLeft(
                ['post_table' => $this->getTable(ResourcePost::BLOG_POST_TABLE)],
                'post_tag_table.post_id = post_table.id',
                []
            )
            ->where('post_table.status = ?', PostStatus::PUBLICATION)
            ->where(
                'post_table.publish_date <= ?',
                $this->dateTime->gmtDate(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
            );
        return $this;
    }

    /**
     * Add category filter
     *
     * @param int|array $category
     * @return $this
     */
    public function addCategoryFilter($category)
    {
        if (!is_array($category)) {
            $category = [$category];
        }
        $this->addFilter('category_id', ['in' => $category], 'public');
        return $this;
    }

    /**
     * Join number of posts
     *
     * @param int $storeId
     * @return $this
     */
    public function joinPostCount($storeId)
    {
        if (!$this->getFlag('post_count_joined')) {
            $this->getSelect()
                ->joinLeft(
                    ['post_store_table' => $this->getTable(ResourcePost::BLOG_POST_STORE_TABLE)],
                    'post_tag_table.post_id = post_store_table.post_id',
                    []
                )
                ->columns(
                    [TagCloudItemInterface::POST_COUNT => new \Zend_Db_Expr('count(post_store_table.post_id)')]
                )
                ->where('post_store_table.store_id IN(?)', [$storeId, Store::DEFAULT_STORE_ID])
                ->group('main_table.id');
            $this->setFlag('post_count_joined', true);
        }
        return $this;
    }

    /**
     * Retrieves maximal number of posts
     *
     * @return int
     */
    public function getMaxPostCount()
    {
        $select = clone $this->getSelect();
        $select->reset(\Magento\Framework\DB\Select::ORDER);
        $select->order(TagCloudItemInterface::POST_COUNT . ' DESC');

        $row = $this->getConnection()->fetchRow($select);
        return isset($row[TagCloudItemInterface::POST_COUNT]) ?
            (int)$row[TagCloudItemInterface::POST_COUNT] :
            0;
    }

    /**
     * Retrieves minimal number of posts
     *
     * @return int
     */
    public function getMinPostCount()
    {
        $select = clone $this->getSelect();
        $select->reset(\Magento\Framework\DB\Select::ORDER);
        $select->order(TagCloudItemInterface::POST_COUNT . ' ASC');

        $row = $this->getConnection()->fetchRow($select);
        return isset($row[TagCloudItemInterface::POST_COUNT]) ?
            (int)$row[TagCloudItemInterface::POST_COUNT] :
            0;
    }

    /**
     * {@inheritdoc}
     */
    protected function _renderFiltersBefore()
    {
        $this->joinPostCategoryTable();
        parent::_renderFiltersBefore();
    }

    /**
     * Join to post and category linkage tables if category filter is applied
     *
     * @return void
     */
    private function joinPostCategoryTable()
    {
        if ($this->getFilter('category_id')) {
            $select = $this->getSelect();
            $select->joinLeft(
                ['post_category_table' => $this->getTable(ResourcePost::BLOG_POST_CATEGORY_TABLE)],
                'post_tag_table.post_id = post_category_table.post_id',
                []
            )->group('main_table.id');
        }
    }
}
