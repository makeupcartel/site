<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

class CreateAttributeTables
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $this->createAttributeTable($setup);
        $this->createAttributeOptionTable($setup);
        $this->createAttributeValueTable($setup);
    }

    /**
     * create amasty_amlocator_attribute table
     *
     * @param SchemaSetupInterface $setup
     */
    private function createAttributeTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()
            ->newTable($setup->getTable('amasty_amlocator_attribute'))
            ->addColumn(
                'attribute_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Attribute Id'
            )->addColumn(
                'frontend_label',
                Table::TYPE_TEXT,
                255,
                ['unsigned' => true, 'nullable' => false],
                'Default Label'
            )
            ->addColumn(
                'attribute_code',
                Table::TYPE_TEXT,
                255,
                ['unsigned' => true, 'nullable' => false],
                'Attribute Code'
            )
            ->addColumn(
                'frontend_input',
                Table::TYPE_TEXT,
                50,
                ['unsigned' => true, 'nullable' => false],
                'Frontend Input'
            )
            ->addColumn(
                'is_required',
                Table::TYPE_SMALLINT,
                5,
                ['unsigned' => true, 'nullable' => true],
                'Is Required'
            )
            ->addColumn(
                'label_serialized',
                Table::TYPE_TEXT,
                '64k',
                ['unsigned' => true, 'nullable' => true],
                'Attribute Labels by store'
            );
        $setup->getConnection()->createTable($table);
    }

    /**
     * create amasty_amlocator_attribute_option table
     *
     * @param SchemaSetupInterface $setup
     */
    private function createAttributeOptionTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()
            ->newTable($setup->getTable('amasty_amlocator_attribute_option'))
            ->addColumn(
                'value_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Value Id'
            )->addColumn(
                'attribute_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Attribute Id'
            )
            ->addColumn(
                'options_serialized',
                Table::TYPE_TEXT,
                '64k',
                ['unsigned' => true, 'nullable' => true],
                'Value And Store'
            )
            ->addColumn(
                'is_default',
                Table::TYPE_TEXT,
                '64k',
                ['unsigned' => true, 'nullable' => true],
                'This is Default Option'
            )
            ->addColumn(
                'sort_order',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Sort order'
            )
            ->addIndex(
                $setup->getIdxName('amasty_amlocator_attribute_option', ['attribute_id']),
                ['attribute_id']
            )
            ->addForeignKey(
                $setup->getFkName(
                    'amasty_amlocator_attribute_option',
                    'attribute_id',
                    'amasty_amlocator_attribute',
                    'attribute_id'
                ),
                'attribute_id',
                $setup->getTable('amasty_amlocator_attribute'),
                'attribute_id',
                Table::ACTION_CASCADE
            );
        $setup->getConnection()->createTable($table);
    }

    /**
     * create amasty_amlocator_store_attribute table
     *
     * @param SchemaSetupInterface $setup
     */
    private function createAttributeValueTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()
            ->newTable($setup->getTable('amasty_amlocator_store_attribute'))
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )
            ->addColumn(
                'attribute_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Attribute Id'
            )
            ->addColumn(
                'store_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Store Location Id'
            )
            ->addColumn(
                'value',
                Table::TYPE_TEXT,
                255,
                ['unsigned' => true, 'nullable' => false],
                'Attribute Value'
            )
            ->addIndex(
                $setup->getIdxName('amasty_amlocator_store_attribute', ['attribute_id']),
                ['attribute_id']
            )
            ->addForeignKey(
                $setup->getFkName(
                    'amasty_amlocator_store_attribute',
                    'attribute_id',
                    'amasty_amlocator_attribute',
                    'attribute_id'
                ),
                'attribute_id',
                $setup->getTable('amasty_amlocator_attribute'),
                'attribute_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName(
                    'amasty_amlocator_store_attribute',
                    'store_id',
                    'amasty_amlocator_location',
                    'id'
                ),
                'store_id',
                $setup->getTable('amasty_amlocator_location'),
                'id',
                Table::ACTION_CASCADE
            )
            ->addIndex(
                $setup->getIdxName(
                    'amasty_amlocator_store_attribute',
                    ['attribute_id', 'store_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['attribute_id', 'store_id'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            );
        $setup->getConnection()->createTable($table);
    }
}
