<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var Operation\ChangeIdFieldType
     */
    private $changeIdFieldType;

    public function __construct(
        \Amasty\Xsearch\Setup\Operation\ChangeIdFieldType $changeIdFieldType
    ) {
        $this->changeIdFieldType = $changeIdFieldType;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.12.0', '<')) {
            $this->addUserInfoTable($setup);
        }

        if (version_compare($context->getVersion(), '1.12.1', '<')) {
            $this->changeIdFieldType->execute($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addUserInfoTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()->newTable(
            $setup->getTable('amasty_xsearch_users_search')
        )->addColumn(
            'id',
            Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Page ID'
        )->addColumn(
            'user_key',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'User'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'query_id',
            Table::TYPE_INTEGER,
            255,
            ['nullable' => false, 'default' => '0'],
            'Query Id'
        )->addColumn(
            'product_click',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => true],
            'Click On Product'
        );

        $setup->getConnection()->createTable($table);
    }
}
