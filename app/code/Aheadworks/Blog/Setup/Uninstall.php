<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;
use Aheadworks\Blog\Model\ResourceModel\Category as ResourceCategory;
use Aheadworks\Blog\Model\ResourceModel\Author as ResourceAuthor;
use Aheadworks\Blog\Model\ResourceModel\Post as ResourcePost;
use Aheadworks\Blog\Model\ResourceModel\Indexer\ProductPost as ResourceProductPost;
use Aheadworks\Blog\Model\ResourceModel\Tag as ResourceTag;

/**
 * Class Uninstall
 *
 * @package Aheadworks\Blog\Setup
 */
class Uninstall implements UninstallInterface
{
    /**
     * {@inheritdoc}
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $this
            ->uninstallTables($installer)
            ->uninstallConfigData($installer)
        ;

        $installer->endSetup();
    }

    /**
     * Uninstall all module tables
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    private function uninstallTables(SchemaSetupInterface $installer)
    {
        $installer->getConnection()->dropTable(
            $installer->getTable(ResourceAuthor::BLOG_AUTHOR_TABLE)
        );
        $installer->getConnection()->dropTable(
            $installer->getTable(ResourceAuthor::BLOG_AUTHOR_POST_TABLE)
        );
        $installer->getConnection()->dropTable(
            $installer->getTable(ResourceCategory::BLOG_CATEGORY_TABLE)
        );
        $installer->getConnection()->dropTable(
            $installer->getTable(ResourceCategory::BLOG_CATEGORY_STORE_TABLE)
        );
        $installer->getConnection()->dropTable(
            $installer->getTable(ResourcePost::BLOG_POST_TABLE)
        );
        $installer->getConnection()->dropTable(
            $installer->getTable(ResourcePost::BLOG_POST_CATEGORY_TABLE)
        );
        $installer->getConnection()->dropTable(
            $installer->getTable(ResourcePost::BLOG_POST_STORE_TABLE)
        );
        $installer->getConnection()->dropTable(
            $installer->getTable(ResourcePost::BLOG_POST_TAG_TABLE)
        );
        $installer->getConnection()->dropTable(
            $installer->getTable(ResourceProductPost::BLOG_PRODUCT_POST_TABLE)
        );
        $installer->getConnection()->dropTable(
            $installer->getTable(ResourceProductPost::BLOG_PRODUCT_POST_INDEX_TABLE)
        );
        $installer->getConnection()->dropTable(
            $installer->getTable(ResourceProductPost::BLOG_PRODUCT_POST_TMP_TABLE)
        );
        $installer->getConnection()->dropTable(
            $installer->getTable(ResourceTag::BLOG_TAG_TABLE)
        );
        return $this;
    }

    /**
     * Uninstall module data from config
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    private function uninstallConfigData(SchemaSetupInterface $installer)
    {
        $configTable = $installer->getTable('core_config_data');
        $installer->getConnection()->delete($configTable, "`path` LIKE 'aw_blog%'");
        return $this;
    }
}
