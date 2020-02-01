<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

namespace Amasty\Groupcat\Setup;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

trait GroupcatTableInitTrate
{
    /**
     * Create Table 'amasty_groupcat_rule'
     * This method is called from UpgradeSchema is module has installed and moduleVersion < 1.2
     * And from InstallSchema
     *
     * @param SchemaSetupInterface $installer
     *
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function createGroupcatRuleTable(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()
            ->newTable($installer->getTable('amasty_groupcat_rule'))
            ->addColumn(
                'rule_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Rule Id'
            )
            ->addColumn(
                'name',
                Table::TYPE_TEXT,
                255,
                ['default' => null, 'nullable' => false],
                'Name'
            )
            ->addColumn(
                'is_active',
                Table::TYPE_BOOLEAN,
                null,
                ['default' => 1, 'nullable' => false],
                'Is Active'
            )
            ->addColumn(
                'conditions_serialized',
                Table::TYPE_TEXT,
                '2M',
                [],
                'Products Conditions serialized'
            )
            /** @since 1.3.0 added Customer Conditions serialized*/
            ->addColumn(
                'actions_serialized',
                Table::TYPE_TEXT,
                '2M',
                [],
                'Customer Conditions serialized'
            )
            ->addColumn(
                'forbidden_action',
                Table::TYPE_SMALLINT,
                null,
                ['default' => 0, 'nullable' => false, 'unsigned' => true],
                'Action on forbid'
            )
            ->addColumn(
                'forbidden_page_id',
                Table::TYPE_SMALLINT,
                null,
                ['default' => null, 'nullable' => true],
                'CMS page on forbid'
            )
            ->addColumn(
                'allow_direct_links',
                Table::TYPE_BOOLEAN,
                null,
                ['default' => 1, 'nullable' => false],
                'Allow direct links or not flag'
            )
            ->addColumn(
                'hide_product',
                Table::TYPE_BOOLEAN,
                null,
                ['default' => 0, 'nullable' => false],
                'Remove Product or not flag'
            )
            ->addColumn(
                'hide_category',
                Table::TYPE_BOOLEAN,
                null,
                ['default' => 0, 'nullable' => false],
                'Remove Category or not flag'
            )
            ->addColumn(
                'hide_cart',
                Table::TYPE_SMALLINT,
                null,
                ['default' => 0, 'nullable' => false, 'unsigned' => true],
                'Hide cart and show Price flag'
            )
            ->addColumn(
                'price_action',
                Table::TYPE_SMALLINT,
                null,
                ['default' => 0, 'nullable' => false, 'unsigned' => true],
                'Hide price, replace with block_id or show'
            )
            ->addColumn(
                'block_id_view',
                Table::TYPE_SMALLINT,
                null,
                ['default' => null, 'nullable' => true],
                'CMS block ID for price block replacement on product view'
            )
            ->addColumn(
                'block_id_list',
                Table::TYPE_SMALLINT,
                null,
                ['default' => null, 'nullable' => true],
                'CMS block ID for price block replacement on product list'
            )
            ->addColumn(
                'stock_status',
                Table::TYPE_SMALLINT,
                null,
                ['default' => 0, 'nullable' => false, 'unsigned' => true],
                'Stock status'
            )
            ->addColumn(
                'from_date',
                Table::TYPE_DATE,
                null,
                ['default' => null, 'nullable' => true],
                'From Date'
            )
            ->addColumn(
                'to_date',
                Table::TYPE_DATE,
                null,
                ['default' => null, 'nullable' => true],
                'To Date'
            )
            ->addColumn(
                'date_range_enabled',
                Table::TYPE_BOOLEAN,
                null,
                ['default' => 0, 'nullable' => false],
                'Date range enabled'
            )
            ->addColumn(
                'from_price',
                Table::TYPE_DECIMAL,
                [12, 4],
                ['nullable' => false, 'default' => 0.0000],
                'From price'
            )
            ->addColumn(
                'to_price',
                Table::TYPE_DECIMAL,
                [12, 4],
                ['nullable' => false, 'default' => 0.0000],
                'To price'
            )
            ->addColumn(
                'by_price',
                Table::TYPE_SMALLINT,
                null,
                ['default' => 0, 'nullable' => false],
                'By price'
            )
            ->addColumn(
                'price_range_enabled',
                Table::TYPE_BOOLEAN,
                null,
                ['default' => 0, 'nullable' => false],
                'Price range enabled'
            )
            ->addColumn(
                'customer_group_enabled',
                Table::TYPE_BOOLEAN,
                null,
                ['default' => 0, 'nullable' => false],
                'Customer group enabled'
            )
            ->addColumn(
                'priority',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => '0'],
                'Priority of the Rule'
            )
            ->addIndex($installer->getIdxName('amasty_groupcat_rule', 'forbidden_page_id'), 'forbidden_page_id')
            ->addIndex($installer->getIdxName('amasty_groupcat_rule', 'block_id_view'), 'block_id_view')
            ->addIndex($installer->getIdxName('amasty_groupcat_rule', 'block_id_list'), 'block_id_list')
            ->addForeignKey(
                $installer->getFkName(
                    'amasty_groupcat_rule',
                    'forbidden_page_id',
                    'cms_page',
                    'page_id'
                ),
                'forbidden_page_id',
                $installer->getTable('cms_page'),
                'page_id',
                Table::ACTION_SET_DEFAULT
            )
            ->addForeignKey(
                $installer->getFkName(
                    'amasty_groupcat_rule',
                    'block_id_view',
                    'cms_block',
                    'block_id'
                ),
                'block_id_view',
                $installer->getTable('cms_block'),
                'block_id',
                Table::ACTION_SET_DEFAULT
            )
            ->addForeignKey(
                $installer->getFkName(
                    'amasty_groupcat_rule',
                    'block_id_list',
                    'cms_block',
                    'block_id'
                ),
                'block_id_list',
                $installer->getTable('cms_block'),
                'block_id',
                Table::ACTION_SET_DEFAULT
            )
            ->setComment('Customer Group Catalog Rule');
        $installer->getConnection()->createTable($table);
    }
}
