<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

namespace Amasty\Groupcat\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    use GroupcatTableInitTrate;

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (!$context->getVersion() || version_compare($context->getVersion(), '1.2', '<')) {
            $this->createCustomerGroupRelationTable($setup);
            $this->createStoreRelationTable($setup);
            $this->createCategoryRelationTable($setup);
            $this->createProductRelationTable($setup);
        }
        /** @since 1.2.0 DB architecture changed. Relations moved to separate tables. Column Types and names changed */
        if ($context->getVersion() && version_compare($context->getVersion(), '1.2', '<')) {
            $this->renameOldTable($setup);
            $this->createGroupcatRuleTable($setup);
        }

        /** @since 1.3.0 added customer attribute conditions */
        if (version_compare($context->getVersion(), '1.3', '<')) {
            $this->createCustomerRelationTable($setup);
            $this->addCustomerConditions($setup);
        }

        /** @since 1.4.0 added quote request and added functionality to hide compare and wishlist button */
        if (version_compare($context->getVersion(), '1.4', '<')) {
            $this->createRequestTable($setup);
            $this->addColumnsForHideButtons($setup);
        }

        /** @since 1.5.7 added index */
        if (version_compare($context->getVersion(), '1.5.7', '<')) {
            $this->addIndexForGettingRule($setup);
        }

        $setup->endSetup();
    }

    /**
     * Create table 'amasty_groupcat_rule_customer_group'
     *
     * @param SchemaSetupInterface $installer
     */
    private function createCustomerGroupRelationTable(SchemaSetupInterface $installer)
    {
        $tableName = 'amasty_groupcat_rule_customer_group';
        $describe = $installer->getConnection()->describeTable($installer->getTable('customer_group'));
        $table     = $installer->getConnection()
            ->newTable($installer->getTable($tableName))
            ->addColumn(
                'rule_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Rule Id'
            )
            ->addColumn(
                'customer_group_id',
                $describe['customer_group_id']['DATA_TYPE'] == 'int' ? Table::TYPE_INTEGER : Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Customer Group ID'
            )
            ->addForeignKey(
                $installer->getFkName(
                    $tableName,
                    'customer_group_id',
                    'customer_group',
                    'customer_group_id'
                ),
                'customer_group_id',
                $installer->getTable('customer_group'),
                'customer_group_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    $tableName,
                    'rule_id',
                    'amasty_groupcat_rule',
                    'rule_id'
                ),
                'rule_id',
                $installer->getTable('amasty_groupcat_rule'),
                'rule_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Rule Customer Group Relation');

        $installer->getConnection()->createTable($table);
    }

    /**
     * Create table 'amasty_groupcat_rule_store'
     *
     * @param SchemaSetupInterface $installer
     */
    private function createStoreRelationTable(SchemaSetupInterface $installer)
    {
        $tableName = 'amasty_groupcat_rule_store';
        $table     = $installer->getConnection()
            ->newTable($installer->getTable($tableName))
            ->addColumn(
                'rule_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Rule Id'
            )
            ->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Store View ID'
            )
            ->addForeignKey(
                $installer->getFkName(
                    $tableName,
                    'rule_id',
                    'amasty_groupcat_rule',
                    'rule_id'
                ),
                'rule_id',
                $installer->getTable('amasty_groupcat_rule'),
                'rule_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    $tableName,
                    'store_id',
                    'store',
                    'store_id'
                ),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Rule Store relation');

        $installer->getConnection()->createTable($table);
    }

    /**
     * Create table 'amasty_groupcat_rule_category'
     *
     * @param SchemaSetupInterface $installer
     */
    private function createCategoryRelationTable(SchemaSetupInterface $installer)
    {
        $tableName = 'amasty_groupcat_rule_category';
        $table     = $installer->getConnection()
            ->newTable($installer->getTable($tableName))
            ->addColumn(
                'rule_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Rule Id'
            )
            ->addColumn(
                'category_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Catalog Category ID'
            )
            ->addForeignKey(
                $installer->getFkName(
                    $tableName,
                    'rule_id',
                    'amasty_groupcat_rule',
                    'rule_id'
                ),
                'rule_id',
                $installer->getTable('amasty_groupcat_rule'),
                'rule_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    $tableName,
                    'category_id',
                    'catalog_category_entity',
                    'entity_id'
                ),
                'category_id',
                $installer->getTable('catalog_category_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Rule Category Relation');

        $installer->getConnection()->createTable($table);
    }

    /**
     * Create index table 'amasty_groupcat_rule_product'
     *
     * @param SchemaSetupInterface $installer
     */
    private function createProductRelationTable(SchemaSetupInterface $installer)
    {
        $tableName = 'amasty_groupcat_rule_product';
        $table     = $installer->getConnection()
            ->newTable($installer->getTable($tableName))
            ->addColumn(
                'rule_product_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Rule Product Id'
            )
            ->addColumn(
                'rule_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Rule Id'
            )
            ->addColumn(
                'from_time',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'From Time'
            )
            ->addColumn(
                'to_time',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'To time'
            )
            ->addColumn(
                'customer_group_enabled',
                Table::TYPE_BOOLEAN,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Is Customer Group Enabled'
            )
            ->addColumn(
                'customer_group_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Customer Group Id'
            )
            ->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Catalog Product Id'
            )
            ->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Store Id'
            )
            ->addColumn(
                'price_action',
                Table::TYPE_SMALLINT,
                null,
                ['default' => 0, 'nullable' => false, 'unsigned' => true],
                'Hide, show or replace Price with block_id'
            )
            ->addColumn(
                'hide_cart',
                Table::TYPE_SMALLINT,
                null,
                ['default' => 0, 'nullable' => false, 'unsigned' => true],
                'Hide cart and show Price flag'
            )
            ->addColumn(
                'hide_product',
                Table::TYPE_BOOLEAN,
                null,
                ['default' => 0, 'nullable' => false],
                'Remove Product or not flag'
            )
            ->addColumn(
                'priority',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => '0'],
                'Priority of the Rule'
            )
            ->addIndex(
                $installer->getIdxName(
                    $tableName,
                    [
                        'rule_id',
                        'from_time',
                        'to_time',
                        'store_id',
                        'customer_group_enabled',
                        'customer_group_id',
                        'product_id',
                        'priority'
                    ],
                    true
                ),
                [
                    'rule_id',
                    'from_time',
                    'to_time',
                    'store_id',
                    'customer_group_enabled',
                    'customer_group_id',
                    'product_id',
                    'priority'
                ],
                ['type' => 'unique']
            )
            ->addIndex(
                $installer->getIdxName($tableName, ['customer_group_enabled']),
                ['customer_group_enabled']
            )
            ->addIndex(
                $installer->getIdxName($tableName, ['customer_group_id']),
                ['customer_group_id']
            )
            ->addIndex(
                $installer->getIdxName($tableName, ['store_id']),
                ['store_id']
            )
            ->addIndex(
                $installer->getIdxName($tableName, ['from_time']),
                ['from_time']
            )
            ->addIndex(
                $installer->getIdxName($tableName, ['to_time']),
                ['to_time']
            )
            ->addIndex(
                $installer->getIdxName($tableName, ['product_id']),
                ['product_id']
            )
            ->setComment('Rule Catalog Product Matches');

        $installer->getConnection()->createTable($table);
    }

    /**
     * Create index table 'amasty_groupcat_rule_customer'
     *
     * @param SchemaSetupInterface $installer
     */
    private function createCustomerRelationTable(SchemaSetupInterface $installer)
    {
        if ($installer->getConnection()->isTableExists($installer->getTable('amasty_groupcat_rule_customer'))) {
            return;
        }
        $tableName = 'amasty_groupcat_rule_customer';
        $table     = $installer->getConnection()
            ->newTable($installer->getTable($tableName))
            ->addColumn(
                'rule_customer_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Rule Customer Id'
            )
            ->addColumn(
                'rule_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Rule Id'
            )
            ->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Customer Id'
            )
            ->addIndex(
                $installer->getIdxName($tableName, ['customer_id']),
                ['customer_id']
            )
            ->setComment('Rule Customer Matches');

        $installer->getConnection()->createTable($table);
    }

    /**
     * Old Table have relations stored in string and separated by commas.
     *
     * @param SchemaSetupInterface $installer
     */
    private function renameOldTable(SchemaSetupInterface $installer)
    {
        if ($installer->getConnection()->isTableExists($installer->getTable('amasty_amgroupcat_rules'))) {
            $installer->getConnection()->renameTable(
                $installer->getTable('amasty_amgroupcat_rules'),
                $installer->getTable('amasty_amgroupcat_rules_old')
            );
        }
    }

    /**
     * Add Customer conditions
     *
     * @param SchemaSetupInterface $installer
     */
    private function addCustomerConditions(SchemaSetupInterface $installer)
    {
        if ($installer->getConnection()->isTableExists($installer->getTable('amasty_groupcat_rule'))) {
            $installer->getConnection()->addColumn(
                $installer->getTable('amasty_groupcat_rule'),
                'actions_serialized',
                [
                    'TYPE' => Table::TYPE_TEXT,
                    'LENGTH' => '2M',
                    'COMMENT' => 'Customer Conditions serialized'
                ]
            );
        }
    }

    /**
     * Add Request table
     *
     * @param SchemaSetupInterface $installer
     */
    private function createRequestTable(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()
            ->newTable($installer->getTable('amasty_groupcat_request'))
            ->addColumn(
                'request_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Request Id'
            )
            ->addColumn(
                'name',
                Table::TYPE_TEXT,
                255,
                ['default' => null, 'nullable' => false],
                'Name'
            )
            ->addColumn(
                'email',
                Table::TYPE_TEXT,
                255,
                ['default' => null, 'nullable' => false],
                'Email'
            )
            ->addColumn(
                'phone',
                Table::TYPE_TEXT,
                255,
                ['default' => null, 'nullable' => false],
                'Phone'
            )
            ->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => '0'],
                'Product id'
            )
            ->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0],
                'Store id'
            )
            ->addColumn(
                'comment',
                Table::TYPE_TEXT,
                null,
                ['default' => null, 'nullable' => false],
                'Comment'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Creation Time'
            )
            ->addColumn(
                'status',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0],
                'Status'
            )
            ->addColumn(
                'message_text',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => null],
                'Message Text'
            )
            ->setComment('Amasty Groupcat Requests');
        $installer->getConnection()->createTable($table);
    }

    /**
     * Add hide compare and wishlist buttons flag to rule tables
     *
     * @param SchemaSetupInterface $installer
     */
    private function addColumnsForHideButtons(SchemaSetupInterface $installer)
    {
        $tableRule = 'amasty_groupcat_rule';
        $tableRuleProduct = 'amasty_groupcat_rule_product';

        if ($installer->getConnection()->isTableExists($installer->getTable($tableRule))) {
            $installer->getConnection()->addColumn(
                $installer->getTable($tableRule),
                'hide_wishlist',
                [
                    'TYPE' => Table::TYPE_BOOLEAN,
                    'DEFAULT' => 0,
                    'NULLABLE' => false,
                    'COMMENT' => 'Hide Add to wishlist button or not flag'
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable($tableRule),
                'hide_compare',
                [
                    'TYPE' => Table::TYPE_BOOLEAN,
                    'DEFAULT' => 0,
                    'NULLABLE' => false,
                    'COMMENT' => 'Hide Add to compare button or not flag'
                ]
            );
        }

        if ($installer->getConnection()->isTableExists($installer->getTable($tableRuleProduct))) {
            $installer->getConnection()->addColumn(
                $installer->getTable($tableRuleProduct),
                'hide_wishlist',
                [
                    'TYPE' => Table::TYPE_BOOLEAN,
                    'DEFAULT' => 0,
                    'NULLABLE' => false,
                    'COMMENT' => 'Hide Add to wishlist button or not flag'
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable($tableRuleProduct),
                'hide_compare',
                [
                    'TYPE' => Table::TYPE_BOOLEAN,
                    'DEFAULT' => 0,
                    'NULLABLE' => false,
                    'COMMENT' => 'Hide Add to compare button or not flag'
                ]
            );
        }
    }

    private function addIndexForGettingRule(SchemaSetupInterface $installer)
    {
        $ruleProductTable = $installer->getTable('amasty_groupcat_rule_product');
        $ruleCustomer = $installer->getTable('amasty_groupcat_rule_customer');

        $installer->getConnection()->addIndex(
            $ruleProductTable,
            $installer->getIdxName(
                $ruleProductTable,
                [
                    'from_time',
                    'to_time',
                    'product_id',
                    'price_action',
                    'customer_group_enabled',
                    'rule_id',
                    'store_id',
                    'customer_group_id',
                    'priority'
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            ),
            [
                'from_time',
                'to_time',
                'product_id',
                'price_action',
                'customer_group_enabled',
                'rule_id',
                'store_id',
                'customer_group_id',
                'priority'
            ]
        );

        $installer->getConnection()->addIndex(
            $ruleCustomer,
            $installer->getIdxName(
                $ruleCustomer,
                [
                    'rule_id',
                    'customer_id'
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            ),
            [
                'rule_id',
                'customer_id'
            ]
        );
    }
}
