<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Setup\UpgradeSchema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class AddLoginDataTable
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('amasty_sociallogin_sales');
        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )
            ->addColumn(
                'social_id',
                Table::TYPE_TEXT,
                255,
                ['unsigned' => true, 'nullable => false'],
                'Social Id'
            )
            ->addColumn(
                'items',
                Table::TYPE_INTEGER,
                null,
                ['default' => 0, 'nullable' => false],
                'Purchased Items'
            )
            ->addColumn(
                'amount',
                Table::TYPE_FLOAT,
                null,
                ['default' => 0, 'nullable' => false],
                'Purchased Amount'
            )
            ->setComment('Social Login Sales Data');
        $setup->getConnection()->createTable($table);
    }
}
