<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Megamenu
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Megamenu\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class InstallSchema
 *
 * @package Magedelight\Megamenu\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'megamenu_menus'
         */
       
        $table = $installer->getConnection()->newTable(
            $installer->getTable('megamenu_menus')
        )->addColumn(
            'menu_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Menu ID'
        )->addColumn(
            'menu_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Menu Name'
        )->addColumn(
            'menu_design_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Menu Design Type'
        )->addColumn(
            'menu_style',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            'Menu Style'
        )->addColumn(
            'is_active',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1'],
            'Is Menu Active'
        )->addColumn(
            'menu_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1'],
            'Menu Type'
        )->addColumn(
            'creation_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Menu Creation Time'
        )->addColumn(
            'update_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'Menu Modification Time'
        )->addIndex(
            $installer->getIdxName('megamenu_menus', ['menu_id']),
            ['menu_id']
        )->addIndex(
            $setup->getIdxName(
                $installer->getTable('megamenu_menus'),
                ['menu_name'],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            ),
            ['menu_name'],
            ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
        )->setComment(
            'Megamenu Menu Table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'megamenu_menus_store'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('megamenu_menus_store')
        )->addColumn(
            'menu_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'primary' => true],
            'Menu ID'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Store ID'
        )->addIndex(
            $installer->getIdxName('megamenu_menus_store', ['menu_id']),
            ['menu_id']
        )->addForeignKey(
            $installer->getFkName('megamenu_menus_store', 'menu_id', 'megamenu_menus', 'menu_id'),
            'menu_id',
            $installer->getTable('megamenu_menus'),
            'menu_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('megamenu_menus_store', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Menus To Store Linkage Table'
        );
        $installer->getConnection()->createTable($table);
        
        /**
         * Create table 'megamenu_menu_items'
         */
       
        $table = $installer->getConnection()->newTable(
            $installer->getTable('megamenu_menu_items')
        )->addColumn(
            'item_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Item ID'
        )->addColumn(
            'item_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Item Name'
        )->addColumn(
            'item_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Item Type'
        )->addColumn(
            'sort_order',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '0'],
            'Sort Order'
        )->addColumn(
            'item_parent_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '0'],
            'Item Parent Id'
        )->addColumn(
            'menu_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Menu Id'
        )->addColumn(
            'object_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Object Id'
        )->addColumn(
            'item_link',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Item Link'
        )->addColumn(
            'item_columns',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            'Items Columns Json'
        )->addColumn(
            'item_font_icon',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            'Item font Icons'
        )->addColumn(
            'creation_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Item Creation Time'
        )->addColumn(
            'update_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'Item Modification Time'
        )->addIndex(
            $installer->getIdxName('megamenu_menu_items', ['item_id']),
            ['item_id']
        )->addIndex(
            $setup->getIdxName(
                $installer->getTable('megamenu_menu_items'),
                ['item_name'],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            ),
            ['item_name'],
            ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
        )->setComment(
            'Megamenu Item Table'
        );
        $installer->getConnection()->createTable($table);
        
        $installer->endSetup();
    }
}
