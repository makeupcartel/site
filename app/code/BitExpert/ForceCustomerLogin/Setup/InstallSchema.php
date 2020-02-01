<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 *
 * @package BitExpert\ForceCustomerLogin\Setup
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        // Drop table if exists
        $installer->getConnection()->dropTable($installer->getTable('bitexpert_forcelogin_whitelist'));

        $table = $installer->getConnection()
            ->newTable($installer->getTable('bitexpert_forcelogin_whitelist'))
            ->addColumn(
                'whitelist_entry_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'bitExpert Force Customer Login Whitelist Entry ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                5,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                'label',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true]
            )
            ->addColumn(
                'url_rule',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true]
            )
            ->addColumn(
                'strategy',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true]
            )
            ->addColumn(
                'editable',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => true]
            )
            ->addIndex(
                $installer->getIdxName('bitexpert_forcelogin_whitelist', ['label']),
                ['label'],
                ['type' => 'unique']
            )
            ->addIndex(
                $installer->getIdxName('bitexpert_forcelogin_whitelist', ['store_id']),
                ['store_id']
            )
            ->addIndex(
                $installer->getIdxName('bitexpert_forcelogin_whitelist', ['editable']),
                ['editable']
            )
            ->addIndex(
                $installer->getIdxName('bitexpert_forcelogin_whitelist', ['url_rule', 'store_id']),
                ['url_rule'],
                ['store_id'],
                ['unique' => true]
            )
            ->addForeignKey(
                $installer->getFkName('bitexpert_forcelogin_whitelist', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('bitExpert Force Customer Login Whitelist Table');

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
