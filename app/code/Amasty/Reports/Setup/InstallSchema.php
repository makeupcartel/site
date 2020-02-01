<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 * @package Amasty\Reports\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'amasty_reports_customers_customers'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('amasty_reports_customers_customers_daily')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn(
            'period',
            Table::TYPE_DATE,
            null,
            [],
            'Period'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true],
            'Store Id'
        )->addColumn(
            'new_accounts',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'New Accounts'
        )->addColumn(
            'orders',
            Table::TYPE_INTEGER,
            255,
            ['unsigned' => true],
            'Orders'
        )->addColumn(
            'reviews',
            Table::TYPE_INTEGER,
            255,
            ['unsigned' => true],
            'Reviews'
        )->addIndex(
            $installer->getIdxName(
                'amasty_reports_customers_customers_daily',
                ['period', 'store_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['period', 'store_id'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('amasty_reports_customers_customers_daily', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('amasty_reports_customers_customers_daily', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Amasty Customers Daily'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'amasty_reports_customers_customers_weekly'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('amasty_reports_customers_customers_weekly')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn(
            'period',
            Table::TYPE_DATE,
            null,
            [],
            'Period'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true],
            'Store Id'
        )->addColumn(
            'new_accounts',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'New Accounts'
        )->addColumn(
            'orders',
            Table::TYPE_INTEGER,
            255,
            ['unsigned' => true],
            'Orders'
        )->addColumn(
            'reviews',
            Table::TYPE_INTEGER,
            255,
            ['unsigned' => true],
            'Reviews'
        )->addIndex(
            $installer->getIdxName(
                'amasty_reports_customers_customers_weekly',
                ['period', 'store_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['period', 'store_id'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('amasty_reports_customers_customers_weekly', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('amasty_reports_customers_customers_weekly', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Amasty Customers Weekly'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'amasty_reports_customers_customers_monthly'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('amasty_reports_customers_customers_monthly')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn(
            'period',
            Table::TYPE_DATE,
            null,
            [],
            'Period'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true],
            'Store Id'
        )->addColumn(
            'new_accounts',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'New Accounts'
        )->addColumn(
            'orders',
            Table::TYPE_INTEGER,
            255,
            ['unsigned' => true],
            'Orders'
        )->addColumn(
            'reviews',
            Table::TYPE_INTEGER,
            255,
            ['unsigned' => true],
            'Reviews'
        )->addIndex(
            $installer->getIdxName(
                'amasty_reports_customers_customers_monthly',
                ['period', 'store_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['period', 'store_id'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('amasty_reports_customers_customers_monthly', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('amasty_reports_customers_customers_monthly', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Amasty Customers Monthly'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'amasty_reports_customers_customers_yearly'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('amasty_reports_customers_customers_yearly')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn(
            'period',
            Table::TYPE_DATE,
            null,
            [],
            'Period'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true],
            'Store Id'
        )->addColumn(
            'new_accounts',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'New Accounts'
        )->addColumn(
            'orders',
            Table::TYPE_INTEGER,
            255,
            ['unsigned' => true],
            'Orders'
        )->addColumn(
            'reviews',
            Table::TYPE_INTEGER,
            255,
            ['unsigned' => true],
            'Reviews'
        )->addIndex(
            $installer->getIdxName(
                'amasty_reports_customers_customers_yearly',
                ['period', 'store_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['period', 'store_id'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('amasty_reports_customers_customers_yearly', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('amasty_reports_customers_customers_yearly', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Amasty Customers Yearly'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
