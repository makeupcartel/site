<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Setup\Operation;

use Amasty\Reports\Model\ResourceModel\RuleIndex;
use Amasty\Reports\Api\Data\RuleInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class RuleIndexTable
 * @package Amasty\Reports\Setup\Operation
 */
class RuleIndexTable
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable(RuleIndex::MAIN_TABLE);
        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn(
                RuleIndex::RULE_ID,
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Rule ID'
            )->addColumn(
                RuleIndex::STORE_ID,
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Store ID'
            )->addColumn(
                RuleIndex::PRODUCT_ID,
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Product ID'
            )->addIndex(
                $setup->getIdxName(RuleIndex::MAIN_TABLE, [RuleIndex::RULE_ID, RuleIndex::STORE_ID]),
                [RuleIndex::RULE_ID, RuleIndex::STORE_ID]
            )->addForeignKey(
                $setup->getFkName(
                    RuleIndex::MAIN_TABLE,
                    RuleIndex::PRODUCT_ID,
                    'catalog_product_entity',
                    'entity_id'
                ),
                RuleIndex::PRODUCT_ID,
                $setup->getTable('catalog_product_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName(
                    RuleIndex::MAIN_TABLE,
                    RuleIndex::RULE_ID,
                    RuleInterface::TABLE_NAME,
                    RuleInterface::ENTITY_ID
                ),
                RuleIndex::RULE_ID,
                $setup->getTable(RuleInterface::TABLE_NAME),
                RuleInterface::ENTITY_ID,
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName(
                    RuleIndex::MAIN_TABLE,
                    RuleIndex::STORE_ID,
                    'store',
                    'store_id'
                ),
                RuleIndex::STORE_ID,
                $setup->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment(
                'Amasty Reports Rule Index Table'
            );
        $setup->getConnection()->createTable($table);
    }
}
