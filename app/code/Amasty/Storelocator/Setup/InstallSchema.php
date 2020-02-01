<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var Operation\CreateAttributeTables
     */
    private $createAttributeTables;

    public function __construct(\Amasty\Storelocator\Setup\Operation\CreateAttributeTables $createAttributeTables)
    {
        $this->createAttributeTables = $createAttributeTables;
    }

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->createLocationTable($setup);
        $this->createAttributeTables->execute($setup);

        $setup->endSetup();
    }

    /**
     * Create table 'amasty_amlocator_location'
     * @param SchemaSetupInterface $setup
     */
    private function createLocationTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()
            ->newTable($setup->getTable('amasty_amlocator_location'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Block Id'
            )
            ->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Location Name'
            )
            ->addColumn(
                'country',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Location Country'
            )
            ->addColumn(
                'city',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Location City'
            )
            ->addColumn(
                'zip',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Location Zip'
            )
            ->addColumn(
                'address',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Location Address'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => true],
                'Location Status'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => true],
                'Location Status'
            )
            ->addColumn(
                'lat',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '11,8',
                ['nullable' => true],
                'Location Latitude'
            )
            ->addColumn(
                'lng',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '11,8',
                ['nullable' => true],
                'Location Longitude'
            )
            ->addColumn(
                'photo',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Location Photo'
            )
            ->addColumn(
                'marker',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Location Marker'
            )
            ->addColumn(
                'position',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => true],
                'Location Position'
            )
            ->addColumn(
                'state',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Location State'
            )
            ->addColumn(
                'description',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Location Description'
            )
            ->addColumn(
                'phone',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Location Phone'
            )
            ->addColumn(
                'email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Location Email'
            )
            ->addColumn(
                'website',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Location Website'
            )
            ->addColumn(
                'category',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Location Category'
            )
            ->addColumn(
                'actions_serialized',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Actions Serialized'
            )
            ->addColumn(
                'store_img',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['default' => '', 'nullable' => false],
                'Store image'
            )
            ->addColumn(
                'stores',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['default' => '', 'nullable' => false],
                'Stores Ids'
            )
            ->addColumn(
                'schedule',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['default' => '', 'nullable' => false],
                'Stores Schedule'
            )
            ->addColumn(
                'marker_img',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['default' => '', 'nullable' => false],
                'Marker Image'
            )->addColumn(
                'show_schedule',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                null,
                ['unsigned' => true, 'default' => '1', 'nullable' => false],
                'Show schedule'
            )
            ->setComment('Amasty Locations Table');

        $setup->getConnection()->createTable($table);
    }
}
