<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

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

        $this->createSocialLoginTable($installer);

        $installer->endSetup();
    }

    /**
     * @param SchemaSetupInterface $installer
     */
    private function createSocialLoginTable(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()
            ->newTable($installer->getTable('amasty_sociallogin_customers'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                11,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true,
                ],
                'ID'
            )
            ->addColumn(
                'social_id',
                Table::TYPE_TEXT,
                255,
                ['unsigned' => true, 'nullable => false'],
                'Social Id'
            )
            ->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                10,
                ['unsigned' => true, 'nullable => false'],
                'Customer Id'
            )
            ->addColumn(
                'type',
                Table::TYPE_TEXT,
                255,
                ['default' => ''],
                'Type'
            )
            ->addColumn(
                'name',
                Table::TYPE_TEXT,
                255,
                ['default' => ''],
                'Customer Social Name'
            )
            ->addForeignKey(
                $installer->getFkName(
                    'amasty_sociallogin_customers',
                    'customer_id',
                    'customer_entity',
                    'entity_id'
                ),
                'customer_id',
                $installer->getTable('customer_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            );

        $installer->getConnection()->createTable($table);
    }
}
