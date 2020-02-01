<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Setup;

use Aheadworks\Blog\Model\Source\Post\Status as PostStatus;
use Aheadworks\Blog\Setup\Updater\Schema;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Aheadworks\Blog\Model\ResourceModel\Category as ResourceCategory;
use Aheadworks\Blog\Model\ResourceModel\Post as ResourcePost;
use Aheadworks\Blog\Model\ResourceModel\Indexer\ProductPost as ResourceProductPost;
use Aheadworks\Blog\Model\ResourceModel\Tag as ResourceTag;

/**
 * Class InstallSchema
 *
 * @package Aheadworks\Blog\Setup
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $this->createTables($installer);
        $this->updaterSchema->update260($installer);
        $installer->endSetup();
    }

    /**
     * Create tables
     *
     * @param SchemaSetupInterface $installer
     * @throws \Zend_Db_Exception
     */
    private function createTables(SchemaSetupInterface $installer)
    {
        /**
         * Create table 'aw_blog_post'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(ResourcePost::BLOG_POST_TABLE))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Post Id'
            )->addColumn(
                'title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Post Title'
            )->addColumn(
                'url_key',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'URL-Key'
            )->addColumn(
                'featured_image_file',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Featured Image File'
            )->addColumn(
                'featured_image_title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Featured Image Title'
            )->addColumn(
                'featured_image_alt',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Featured Image Alt Text'
            )->addColumn(
                'short_content',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [],
                'Post Short Content'
            )->addColumn(
                'content',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [],
                'Post Content'
            )->addColumn(
                'author_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Author Name'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => PostStatus::DRAFT],
                'Status'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )->addColumn(
                'publish_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                [],
                'Publish Date'
            )->addColumn(
                'canonical_category_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Category ID used for canonical URL'
            )->addColumn(
                'is_allow_comments',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'Is Allowed Comments'
            )->addColumn(
                'meta_title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Meta Title'
            )->addColumn(
                'meta_description',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [],
                'Meta Description'
            )->addColumn(
                'product_condition',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Product Condition'
            )->addColumn(
                'meta_twitter_site',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Meta Twitter Site'
            )->addColumn(
                'meta_twitter_creator',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Meta Twitter Creator'
            )->addColumn(
                'customer_groups',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                ['nullable' => false],
                'Allowed Customer Groups'
            )
            ->addIndex(
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
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Category Id'
            )->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Category Name'
            )->addColumn(
                'url_key',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'URL-Key'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'Status'
            )->addColumn(
                'sort_order',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Sort Order'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )->addColumn(
                'meta_title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Meta Title'
            )->addColumn(
                'meta_description',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
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
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Tag Id'
            )->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Name'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
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
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Category Id'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
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
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
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
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment('Blog Category To Store Relation Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_blog_post_store'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(ResourcePost::BLOG_POST_STORE_TABLE))
            ->addColumn(
                'post_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Post Id'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
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
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(
                    ResourcePost::BLOG_POST_STORE_TABLE,
                    'store_id',
                    'store',
                    'store_id'
                ),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment('Blog Post To Store Relation Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_blog_post_category'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(ResourcePost::BLOG_POST_CATEGORY_TABLE))
            ->addColumn(
                'category_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Category Id'
            )->addColumn(
                'post_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
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
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
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
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment('Blog Post To Category Relation Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_blog_post_tag'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(ResourcePost::BLOG_POST_TAG_TABLE))
            ->addColumn(
                'tag_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Tag Id'
            )->addColumn(
                'post_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
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
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
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
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment('Blog Post To Tag Relation Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_blog_product_post'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(ResourceProductPost::BLOG_PRODUCT_POST_TABLE))
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Product Id'
            )->addColumn(
                'post_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Post Id'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
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
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
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
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment('Blog Product Post');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_blog_product_post_idx'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(ResourceProductPost::BLOG_PRODUCT_POST_INDEX_TABLE))
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Product Id'
            )->addColumn(
                'post_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Post Id'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
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
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Product Id'
            )->addColumn(
                'post_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Post Id'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
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
    }
}
