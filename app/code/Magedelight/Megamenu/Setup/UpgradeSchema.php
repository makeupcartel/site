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

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade the Megamenu module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->addItemClassField($setup);
        }
        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->addCustomerGroupField($setup);
        }
        if (version_compare($context->getVersion(), '2.0.4', '<')) {
            $this->addCategoryDisplayField($setup);
        }
        if (version_compare($context->getVersion(), '2.0.5', '<')) {
            $this->changeCustomerGroupType($setup);
            $this->addVerticalMenuField($setup);
            $this->addMenuBgColorField($setup);
        }
    }

    /**
     * Change customer group type
     *
     * @param SchemaSetupInterface $setup
     */
    protected function changeCustomerGroupType(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->changeColumn(
            $setup->getTable('megamenu_menus'),
            'customer_groups',
            'customer_groups',
            [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'length' => '64k'
                ]
        );
    }

    /**
     * Add category Vertical Menu Enable Field
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    protected function addVerticalMenuField(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('megamenu_menu_items'),
            'category_vertical_menu',
            [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            'length' => 255,
            'nullable' => true,
            'comment' => 'Category Vertical Menu Enable?',
            'after' => 'category_display'
                ]
        );
        return $this;
    }

    /**
     * Add Vertical menu background color
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    protected function addMenuBgColorField(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('megamenu_menu_items'),
            'category_vertical_menu_bg',
            [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'length' => 10,
            'nullable' => true,
            'comment' => 'Category Vertical Menu Bg Color',
            'after' => 'category_vertical_menu'
                ]
        );
        return $this;
    }

    /**
     * Add category display
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    protected function addCategoryDisplayField(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('megamenu_menu_items'),
            'category_display',
            [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            'length' => 255,
            'nullable' => true,
            'comment' => 'Category display',
            'after' => 'animation_option'
                ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('megamenu_menu_items'),
            'category_columns',
            [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'length' => '64k',
            'nullable' => true,
            'comment' => 'Category Columns Json',
            'after' => 'category_display'
                ]
        );
        return $this;
    }

    /**
     * Add menu item class
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    protected function addItemClassField(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('megamenu_menu_items'),
            'item_class',
            [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'length' => 255,
            'nullable' => true,
            'comment' => 'Items Classes',
            'after' => 'item_font_icon'
                ]
        );
        return $this;
    }

    /**
     * Add customergroup field
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    protected function addCustomerGroupField(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('megamenu_menus'),
            'customer_groups',
            [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'length' => 255,
            'nullable' => true,
            'comment' => 'Customer Groups',
            'after' => 'menu_type'
                ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('megamenu_menus'),
            'is_sticky',
            [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'length' => 255,
            'nullable' => true,
            'comment' => 'Is Sticky',
            'after' => 'menu_type'
                ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('megamenu_menus'),
            'menu_design_type',
            [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'length' => 255,
            'nullable' => true,
            'comment' => 'Menu Design Type',
            'after' => 'menu_type'
                ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('megamenu_menu_items'),
            'animation_option',
            [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'length' => 255,
            'nullable' => true,
            'comment' => 'Animation Option',
            'after' => 'item_class'
                ]
        );
        return $this;
    }
}
