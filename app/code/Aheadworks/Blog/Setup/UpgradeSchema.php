<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Setup;

use Aheadworks\Blog\Model\Source\Post\Status as PostStatus;
use Aheadworks\Blog\Model\Source\Post\Status;
use Aheadworks\Blog\Setup\Updater\Schema;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Aheadworks\Blog\Model\ResourceModel\Category as ResourceCategory;
use Aheadworks\Blog\Model\ResourceModel\Post as ResourcePost;
use Aheadworks\Blog\Model\ResourceModel\Indexer\ProductPost as ResourceProductPost;
use Aheadworks\Blog\Model\ResourceModel\Tag as ResourceTag;

/**
 * Upgrade the Blog module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Table postfix for AW Blog 1.0.0
     *
     * @var string
     */
    const OLD_TABLE_SUFFIX = '_old';

    /**
     * List of tables for AW Blog 1.0.0
     *
     * @var array
     */
    private $oldTables = [
        'aw_blog_cat',
        'aw_blog_cat_store',
        'aw_blog_post',
        'aw_blog_post_cat',
        'aw_blog_post_store',
        'aw_blog_post_tag',
        'aw_blog_tag'
    ];

    /**
     * @var Schema
     */
    private $updaterSchema;

    /**
     * @param Schema $schema
     */
    public function __construct(
        Schema $schema
    ) {
        $this->updaterSchema = $schema;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if ($context->getVersion() && version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->renameOldTables($setup);
            $this->addNewTables($setup);
            $this->migrateData($setup);
        }
        if ($context->getVersion() && version_compare($context->getVersion(), '2.1.0', '<')) {
            $this->updatePostStatus($setup);
        }
        if ($context->getVersion() && version_compare($context->getVersion(), '2.4.0', '<')) {
            $this->addFeaturedImageFields($setup);
            $this->addMetaTwitterFields($setup);
            $this->addCanonicalCategoryIdField($setup);
            $this->addCustomerGroupsField($setup);
        }
        if ($context->getVersion() && version_compare($context->getVersion(), '2.4.3', '<')) {
            $this->updateProductIdColumnType($setup);
        }
        if ($context->getVersion() && version_compare($context->getVersion(), '2.6.0', '<')) {
            $this->updaterSchema->update260($setup);
        }
    }

    /**
     * Update product_id column type in tables
     *
     * @param SchemaSetupInterface $setup
     */
    private function updateProductIdColumnType(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->changeColumn(
            $setup->getTable(ResourceProductPost::BLOG_PRODUCT_POST_TABLE),
            'product_id',
            'product_id',
            [
                'type' => Table::TYPE_INTEGER,
                'length' => null,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Product Id'
            ]
        );

        $setup->getConnection()->changeColumn(
            $setup->getTable(ResourceProductPost::BLOG_PRODUCT_POST_INDEX_TABLE),
            'product_id',
            'product_id',
            [
                'type' => Table::TYPE_INTEGER,
                'length' => null,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Product Id'
            ]
        );

        $setup->getConnection()->changeColumn(
            $setup->getTable(ResourceProductPost::BLOG_PRODUCT_POST_TMP_TABLE),
            'product_id',
            'product_id',
            [
                'type' => Table::TYPE_INTEGER,
                'length' => null,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Product Id'
            ]
        );
    }

    /**
     * Rename old tables
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    private function renameOldTables(SchemaSetupInterface $installer)
    {
        $tablePairs = [];
        foreach ($this->oldTables as $tableName) {
            $newTableName = $installer->getTable($tableName . self::OLD_TABLE_SUFFIX);
            if (!$installer->getConnection()->isTableExists($newTableName)) {
                $tablePairs[] = ['oldName' => $installer->getTable($tableName), 'newName' => $newTableName];
            }
        }
        if (count($tablePairs)) {
            $installer->getConnection()->renameTablesBatch($tablePairs);
        }

        return $this;
    }

    /**
     * Add new tables
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function addNewTables(SchemaSetupInterface $installer)
    {
        /**
         * Create table 'aw_blog_post'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(ResourcePost::BLOG_POST_TABLE))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Post Id'
            )->addColumn(
                'title',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Post Title'
            )->addColumn(
                'url_key',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'URL-Key'
            )->addColumn(
                'short_content',
                Table::TYPE_TEXT,
                '2M',
                [],
                'Post Short Content'
            )->addColumn(
                'content',
                Table::TYPE_TEXT,
                null,
                [],
                'Post Content'
            )->addColumn(
                'author_name',
                Table::TYPE_TEXT,
                255,
                [],
                'Author Name'
            )->addColumn(
                'status',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => PostStatus::DRAFT],
                'Status'
            )->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )->addColumn(
                'publish_date',
                Table::TYPE_DATETIME,
                null,
                [],
                'Publish Date'
            )->addColumn(
                'is_allow_comments',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'Is Allowed Comments'
            )->addColumn(
                'meta_title',
                Table::TYPE_TEXT,
                255,
                [],
                'Meta Title'
            )->addColumn(
                'meta_description',
                Table::TYPE_TEXT,
                '2M',
                [],
                'Meta Description'
            )->addColumn(
                'product_condition',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Product Condition'
            )->addIndex(
                $installer->getIdxName(ResourcePost::BLOG_POST_TABLE, ['status', 'publish_date']),
                ['status', 'publish_date']
            )->addIndex(
                $installer->getIdxName(ResourcePost::BLOG_POST_TABLE, ['url_key']),
                ['url_key']
            )->setComment('Blog Post');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_blog_category'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(ResourceCategory::BLOG_CATEGORY_TABLE))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Category Id'
            )->addColumn(
                'name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Category Name'
            )->addColumn(
                'url_key',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'URL-Key'
            )->addColumn(
                'status',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'Status'
            )->addColumn(
                'sort_order',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Sort Order'
            )->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )->addColumn(
                'meta_title',
                Table::TYPE_TEXT,
                255,
                [],
                'Meta Title'
            )->addColumn(
                'meta_description',
                Table::TYPE_TEXT,
                '2M',
                [],
                'Meta Description'
            )->addIndex(
                $installer->getIdxName(ResourceCategory::BLOG_CATEGORY_TABLE, ['status']),
                ['status']
            )->addIndex(
                $installer->getIdxName(ResourceCategory::BLOG_CATEGORY_TABLE, ['url_key']),
                ['url_key']
            )->setComment('Blog Category');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_blog_tag'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(ResourceTag::BLOG_TAG_TABLE))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Tag Id'
            )->addColumn(
                'name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Name'
            )->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )->addIndex(
                $installer->getIdxName(ResourceTag::BLOG_TAG_TABLE, ['name']),
                ['name']
            )->setComment('Blog Tag');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_blog_category_store'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(ResourceCategory::BLOG_CATEGORY_STORE_TABLE))
            ->addColumn(
                'category_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Category Id'
            )->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Store Id'
            )->addIndex(
                $installer->getIdxName(ResourceCategory::BLOG_CATEGORY_STORE_TABLE, ['category_id']),
                ['category_id']
            )->addIndex(
                $installer->getIdxName(ResourceCategory::BLOG_CATEGORY_STORE_TABLE, ['store_id']),
                ['store_id']
            )->addForeignKey(
                $installer->getFkName(
                    ResourceCategory::BLOG_CATEGORY_STORE_TABLE,
                    'category_id',
                    ResourceCategory::BLOG_CATEGORY_TABLE,
                    'id'
                ),
                'category_id',
                $installer->getTable(ResourceCategory::BLOG_CATEGORY_TABLE),
                'id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(
                    ResourceCategory::BLOG_CATEGORY_STORE_TABLE,
                    'store_id',
                    'store',
                    'store_id'
                ),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )->setComment('Blog Category To Store Relation Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_blog_post_store'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(ResourcePost::BLOG_POST_STORE_TABLE))
            ->addColumn(
                'post_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Post Id'
            )->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Store Id'
            )->addIndex(
                $installer->getIdxName(ResourcePost::BLOG_POST_STORE_TABLE, ['post_id']),
                ['post_id']
            )->addIndex(
                $installer->getIdxName(ResourcePost::BLOG_POST_STORE_TABLE, ['store_id']),
                ['store_id']
            )->addForeignKey(
                $installer->getFkName(
                    ResourcePost::BLOG_POST_STORE_TABLE,
                    'post_id',
                    ResourcePost::BLOG_POST_TABLE,
                    'id'
                ),
                'post_id',
                $installer->getTable(ResourcePost::BLOG_POST_TABLE),
                'id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName('aw_blog_post_store_new', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )->setComment('Blog Post To Store Relation Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_blog_post_category'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(ResourcePost::BLOG_POST_CATEGORY_TABLE))
            ->addColumn(
                'category_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Category Id'
            )->addColumn(
                'post_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Post Id'
            )->addIndex(
                $installer->getIdxName(ResourcePost::BLOG_POST_CATEGORY_TABLE, ['category_id']),
                ['category_id']
            )->addIndex(
                $installer->getIdxName(ResourcePost::BLOG_POST_CATEGORY_TABLE, ['post_id']),
                ['post_id']
            )->addForeignKey(
                $installer->getFkName(
                    ResourcePost::BLOG_POST_CATEGORY_TABLE,
                    'category_id',
                    ResourceCategory::BLOG_CATEGORY_TABLE,
                    'id'
                ),
                'category_id',
                $installer->getTable(ResourceCategory::BLOG_CATEGORY_TABLE),
                'id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(
                    ResourcePost::BLOG_POST_CATEGORY_TABLE,
                    'post_id',
                    ResourcePost::BLOG_POST_TABLE,
                    'id'
                ),
                'post_id',
                $installer->getTable(ResourcePost::BLOG_POST_TABLE),
                'id',
                Table::ACTION_CASCADE
            )->setComment('Blog Post To Category Relation Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_blog_post_tag'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(ResourcePost::BLOG_POST_TAG_TABLE))
            ->addColumn(
                'tag_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Tag Id'
            )->addColumn(
                'post_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Post Id'
            )->addIndex(
                $installer->getIdxName(ResourcePost::BLOG_POST_TAG_TABLE, ['tag_id']),
                ['tag_id']
            )->addIndex(
                $installer->getIdxName(ResourcePost::BLOG_POST_TAG_TABLE, ['post_id']),
                ['post_id']
            )->addForeignKey(
                $installer->getFkName(
                    ResourcePost::BLOG_POST_TAG_TABLE,
                    'tag_id',
                    ResourceTag::BLOG_TAG_TABLE,
                    'id'
                ),
                'tag_id',
                $installer->getTable(ResourceTag::BLOG_TAG_TABLE),
                'id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(
                    ResourcePost::BLOG_POST_TAG_TABLE,
                    'post_id',
                    ResourcePost::BLOG_POST_TABLE,
                    'id'
                ),
                'post_id',
                $installer->getTable(ResourcePost::BLOG_POST_TABLE),
                'id',
                Table::ACTION_CASCADE
            )->setComment('Blog Post To Tag Relation Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_blog_product_post'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(ResourceProductPost::BLOG_PRODUCT_POST_TABLE))
            ->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Product Id'
            )->addColumn(
                'post_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Post Id'
            )->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Store Id'
            )->addIndex(
                $installer->getIdxName(ResourceProductPost::BLOG_PRODUCT_POST_TABLE, ['product_id']),
                ['product_id']
            )->addIndex(
                $installer->getIdxName(ResourceProductPost::BLOG_PRODUCT_POST_TABLE, ['post_id']),
                ['post_id']
            )->addIndex(
                $installer->getIdxName(ResourceProductPost::BLOG_PRODUCT_POST_TABLE, ['store_id']),
                ['store_id']
            )->addForeignKey(
                $installer->getFkName(
                    ResourceProductPost::BLOG_PRODUCT_POST_TABLE,
                    'post_id',
                    ResourcePost::BLOG_POST_TABLE,
                    'id'
                ),
                'post_id',
                $installer->getTable(ResourcePost::BLOG_POST_TABLE),
                'id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(
                    ResourceProductPost::BLOG_PRODUCT_POST_TABLE,
                    'store_id',
                    'store',
                    'store_id'
                ),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )->setComment('Blog Product Post');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_blog_product_post_idx'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(ResourceProductPost::BLOG_PRODUCT_POST_INDEX_TABLE))
            ->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Product Id'
            )->addColumn(
                'post_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Post Id'
            )->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Store Id'
            )->addIndex(
                $installer->getIdxName(ResourceProductPost::BLOG_PRODUCT_POST_INDEX_TABLE, ['product_id']),
                ['product_id']
            )->addIndex(
                $installer->getIdxName(ResourceProductPost::BLOG_PRODUCT_POST_INDEX_TABLE, ['post_id']),
                ['post_id']
            )->addIndex(
                $installer->getIdxName(ResourceProductPost::BLOG_PRODUCT_POST_INDEX_TABLE, ['store_id']),
                ['store_id']
            )->setComment('Blog Product Post Indexer Idx');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_blog_product_post_tmp'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(ResourceProductPost::BLOG_PRODUCT_POST_TMP_TABLE))
            ->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Product Id'
            )->addColumn(
                'post_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Post Id'
            )->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Store Id'
            )->addIndex(
                $installer->getIdxName(ResourceProductPost::BLOG_PRODUCT_POST_TMP_TABLE, ['product_id']),
                ['product_id']
            )->addIndex(
                $installer->getIdxName(ResourceProductPost::BLOG_PRODUCT_POST_TMP_TABLE, ['post_id']),
                ['post_id']
            )->addIndex(
                $installer->getIdxName(ResourceProductPost::BLOG_PRODUCT_POST_TMP_TABLE, ['store_id']),
                ['store_id']
            )->setComment('Blog Product Post Indexer Tmp');
        $installer->getConnection()->createTable($table);

        return $this;
    }

    /**
     * Migrate data from old table to new table
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function migrateData(SchemaSetupInterface $installer)
    {
        // Migrate category data
        $oldNewCategoryId = [];
        $connection = $installer->getConnection();
        $select = $connection->select()
            ->from($installer->getTable('aw_blog_cat' . self::OLD_TABLE_SUFFIX));
        $categoryData = $connection->fetchAssoc($select);
        foreach ($categoryData as $category) {
            $toInsertCategory = [
                'name'             => $category['name'],
                'url_key'          => $category['url_key'],
                'status'           => $category['status'],
                'sort_order'       => $category['sort_order'],
                'meta_title'       => $category['meta_title'],
                'meta_description' => $category['meta_description']
            ];
            $connection->insert(
                $installer->getTable(ResourceCategory::BLOG_CATEGORY_TABLE),
                $toInsertCategory
            );
            $newCategoryId = $connection->lastInsertId();
            $oldNewCategoryId[$category['cat_id']] = $newCategoryId;

            $select = $connection->select()
                ->from($installer->getTable('aw_blog_cat_store' . self::OLD_TABLE_SUFFIX))
                ->where('cat_id = :id');
            $categoryStoreData = $connection->fetchAll($select, ['id' => $category['cat_id']]);
            $toInsertCategoryStore = [];
            foreach ($categoryStoreData as $categoryStore) {
                $toInsertCategoryStore[] = [
                    'category_id' => $newCategoryId,
                    'store_id'    => $categoryStore['store_id']
                ];
            }
            if (count($toInsertCategoryStore)) {
                $connection->insertMultiple(
                    $installer->getTable(ResourceCategory::BLOG_CATEGORY_STORE_TABLE),
                    $toInsertCategoryStore
                );
            }
        }

        // Migrate post data
        $oldNewPostId = [];
        $connection = $installer->getConnection();
        $select = $connection->select()
            ->from($installer->getTable(ResourcePost::BLOG_POST_TABLE . self::OLD_TABLE_SUFFIX));
        $postData = $connection->fetchAssoc($select);
        foreach ($postData as $post) {
            $toInsertPost = [
                'title'             => $post['title'],
                'url_key'           => $post['url_key'],
                'short_content'     => $post['short_content'],
                'content'           => $post['content'],
                'author_name'       => $post['author_name'],
                'status'            => $post['status'],
                'created_at'        => $post['created_at'],
                'updated_at'        => $post['updated_at'],
                'publish_date'      => $post['publish_date'],
                'is_allow_comments' => $post['is_allow_comments'],
                'meta_title'        => $post['meta_title'],
                'meta_description'  => $post['meta_description']
            ];
            $connection->insert(
                $installer->getTable(ResourcePost::BLOG_POST_TABLE),
                $toInsertPost
            );
            $newPostId = $connection->lastInsertId();
            $oldNewPostId[$post['post_id']] = $newPostId;

            // Migrate Post-Store data
            $select = $connection->select()
                ->from($installer->getTable(ResourcePost::BLOG_POST_STORE_TABLE . self::OLD_TABLE_SUFFIX))
                ->where('post_id = :id');
            $postStoreData = $connection->fetchAll($select, ['id' => $post['post_id']]);
            $toInsertPostStore = [];
            foreach ($postStoreData as $postStore) {
                $toInsertPostStore[] = [
                    'post_id'  => $newPostId,
                    'store_id' => $postStore['store_id']
                ];
            }
            if (count($toInsertPostStore)) {
                $connection->insertMultiple(
                    $installer->getTable(ResourcePost::BLOG_POST_STORE_TABLE),
                    $toInsertPostStore
                );
            }
        }

        // Migrate tag data
        $oldNewTagId = [];
        $connection = $installer->getConnection();
        $select = $connection->select()
            ->from($installer->getTable(ResourceTag::BLOG_TAG_TABLE . self::OLD_TABLE_SUFFIX));
        $tagData = $connection->fetchAssoc($select);
        foreach ($tagData as $tag) {
            $toInsertTag = [
                'name' => $tag['name']
            ];
            $connection->insert(
                $installer->getTable(ResourceTag::BLOG_TAG_TABLE),
                $toInsertTag
            );
            $newTagId =$connection->lastInsertId();
            $oldNewTagId[$tag['id']] = $newTagId;
        }

        // Migrate Post-Tag data
        foreach ($oldNewTagId as $oldTagId => $newTagId) {
            $select = $connection->select()
                ->from($installer->getTable(ResourcePost::BLOG_POST_TAG_TABLE . self::OLD_TABLE_SUFFIX))
                ->where('tag_id = :id');
            $postTagData = $connection->fetchAll($select, ['id' => $oldTagId]);
            $toInsertPostTag = [];
            foreach ($postTagData as $postTag) {
                $toInsertPostTag[] = [
                    'post_id' => $oldNewPostId[$postTag['post_id']],
                    'tag_id' => $newTagId
                ];
            }
            if (count($toInsertPostTag)) {
                $connection->insertMultiple(
                    $installer->getTable(ResourcePost::BLOG_POST_TAG_TABLE),
                    $toInsertPostTag
                );
            }
        }

        // Migrate Post-Category data
        foreach ($oldNewCategoryId as $oldCategoryId => $newCategoryId) {
            $select = $connection->select()
                ->from($installer->getTable('aw_blog_post_cat' . self::OLD_TABLE_SUFFIX))
                ->where('cat_id = :id');
            $postCategoryData = $connection->fetchAll($select, ['id' => $oldCategoryId]);
            $toInsertPostCategory = [];
            foreach ($postCategoryData as $postCategory) {
                $toInsertPostCategory[] = [
                    'post_id' => $oldNewPostId[$postCategory['post_id']],
                    'category_id' => $newCategoryId
                ];
            }
            if (count($toInsertPostCategory)) {
                $connection->insertMultiple(
                    $installer->getTable(ResourcePost::BLOG_POST_CATEGORY_TABLE),
                    $toInsertPostCategory
                );
            }
        }
        return $this;
    }

    /**
     * Update post status
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    private function updatePostStatus(SchemaSetupInterface $installer)
    {
        $connection = $installer->getConnection();

        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $select = $connection->select()
            ->from($installer->getTable(ResourcePost::BLOG_POST_TABLE), ['id'])
            ->where('publish_date > ?', $now);
        $postIds = $connection->fetchCol($select);

        if (count($postIds)) {
            $connection->update(
                $installer->getTable(ResourcePost::BLOG_POST_TABLE),
                ['status' => Status::SCHEDULED],
                'id IN(' . implode(',', array_values($postIds)) . ')'
            );
        }
    }

    /**
     * Add featured image fields to post table
     *
     * @param SchemaSetupInterface $installer
     */
    private function addFeaturedImageFields(SchemaSetupInterface $installer)
    {
        $connection = $installer->getConnection();

        $connection->addColumn(
            $installer->getTable(ResourcePost::BLOG_POST_TABLE),
            'featured_image_alt',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'after' => 'url_key',
                'comment' => 'Featured Image Alt Text'
            ]
        );

        $connection->addColumn(
            $installer->getTable(ResourcePost::BLOG_POST_TABLE),
            'featured_image_title',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'after' => 'url_key',
                'comment' => 'Featured Image Title'
            ]
        );

        $connection->addColumn(
            $installer->getTable(ResourcePost::BLOG_POST_TABLE),
            'featured_image_file',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'after' => 'url_key',
                'comment' => 'Featured Image File',
            ]
        );
    }

    /**
     * Add meta twitter fields to post table
     *
     * @param SchemaSetupInterface $installer
     */
    private function addMetaTwitterFields(SchemaSetupInterface $installer)
    {
        $connection = $installer->getConnection();

        $connection->addColumn(
            $installer->getTable(ResourcePost::BLOG_POST_TABLE),
            'meta_twitter_site',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'comment' => 'Meta Twitter Site'
            ]
        );

        $connection->addColumn(
            $installer->getTable(ResourcePost::BLOG_POST_TABLE),
            'meta_twitter_creator',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'comment' => 'Meta Twitter Creator'
            ]
        );
    }

    /**
     * Add canonical category field to post table
     *
     * @param SchemaSetupInterface $installer
     */
    private function addCanonicalCategoryIdField(SchemaSetupInterface $installer)
    {
        $connection = $installer->getConnection();

        $connection->addColumn(
            $installer->getTable(ResourcePost::BLOG_POST_TABLE),
            'canonical_category_id',
            [
                'type' => Table::TYPE_INTEGER,
                'length' => null,
                'unsigned' => true,
                'after' => 'publish_date',
                'comment' => 'Category ID used for canonical URL'
            ]
        );
    }

    /**
     * Add customer groups field to post table
     *
     * @param SchemaSetupInterface $installer
     */
    private function addCustomerGroupsField(SchemaSetupInterface $installer)
    {
        $connection = $installer->getConnection();

        $connection->addColumn(
            $installer->getTable(ResourcePost::BLOG_POST_TABLE),
            'customer_groups',
            [
                'type' => Table::TYPE_TEXT,
                'length' => '64k',
                'nullable' => false,
                'comment' => 'Allowed Customer Groups'
            ]
        );
    }
}
