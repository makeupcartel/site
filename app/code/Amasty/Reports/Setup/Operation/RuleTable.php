<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Setup\Operation;

use Amasty\Reports\Api\Data\RuleInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class RuleTable
 * @package Amasty\Reports\Setup\Operation
 */
class RuleTable
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable(RuleInterface::TABLE_NAME);
        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn(
                RuleInterface::ENTITY_ID,
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
                'Entity ID'
            )->addColumn(
                RuleInterface::TITLE,
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Rule Title'
            )
            ->addColumn(
                RuleInterface::STATUS,
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Status'
            )
            ->addColumn(
                RuleInterface::UPDATED_AT,
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => true],
                'Last Updated Date'
            )
            ->addColumn(
                RuleInterface::CONDITIONS,
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                ['nullable' => true],
                'Conditions'
            )
            ->addIndex(
                $setup->getIdxName(RuleInterface::TABLE_NAME, [RuleInterface::ENTITY_ID]),
                [RuleInterface::ENTITY_ID]
            )
            ->setComment(
                'Amasty Reports Rule Table'
            );
        $setup->getConnection()->createTable($table);
    }
}
