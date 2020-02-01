<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Setup\Operation;

use \Magento\Framework\Setup\SchemaSetupInterface;

class ChangeIdFieldType
{
    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $tableName = $setup->getTable('amasty_xsearch_users_search');

        $connection->modifyColumn(
            $tableName,
            'id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => false,
                'identity' => true,
                'primary' => true
            ]
        );
        
        $connection->modifyColumn(
            $tableName,
            'query_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => false,
                'default' => '0'
            ]
        );
    }
}
