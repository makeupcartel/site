<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Setup\Operation;

use Amasty\Reports\Model\ResourceModel\Abandoned\Cart;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class AbandonedCart
 * @package Amasty\Reports\Setup\Operation
 */
class AbandonedCart
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable(Cart::MAIN_TABLE);
        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn(
                Cart::ID,
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
                'Entity ID'
            )->addColumn(
                Cart::QUOTE_ID,
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'primary' => true, 'unsigned' => true],
                'Quote ID'
            )->addColumn(
                Cart::STORE_ID,
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'unsigned' => true],
                'Store ID'
            )->addColumn(
                Cart::CUSTOMER_NAME,
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Customer Name'
            )->addColumn(
                Cart::STATUS,
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Status'
            )->addColumn(
                Cart::CREATED_AT,
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => true],
                'Created Date'
            )->addColumn(
                Cart::ITEMS_QTY,
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => true],
                'Items Qty'
            )->addColumn(
                Cart::PRODUCTS,
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Products in Quote'
            )->addColumn(
                Cart::GRAND_TOTAL,
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Grand Total'
            )->addColumn(
                Cart::COUPON_CODE,
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Coupon Code'
            )->addColumn(
                Cart::CUSTOMER_ID,
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Customer Id'
            )->setComment(
                'Amasty Reports Abandoned Carts'
            );

        $setup->getConnection()->createTable($table);
    }
}
