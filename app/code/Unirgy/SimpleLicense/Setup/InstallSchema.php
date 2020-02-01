<?php
/**
 * Created by pp
 *
 * @project magento2
 */

namespace Unirgy\SimpleLicense\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{

    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     * @return void
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $tableName = $installer->getTable('usimplelic_license');

        $connection = $installer->getConnection();
        if ($connection->isTableExists($tableName) != true) {
            $table = $connection->newTable($tableName)
                ->addColumn('license_id', Table::TYPE_INTEGER, 10, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true])
                ->addColumn('license_key', Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => ''])
                ->addColumn('license_status', Table::TYPE_TEXT, 50, ['nullable' => false])
                ->addColumn('last_checked', Table::TYPE_DATETIME, null, ['nullable' => true, 'default' => null])
                ->addColumn('last_status', Table::TYPE_TEXT, 20, ['nullable' => true, 'default' => null])
                ->addColumn('last_error', Table::TYPE_TEXT, null, ['nullable' => true])
                ->addColumn('retry_num', Table::TYPE_SMALLINT, 4, ['nullable' => true, 'default' => null])
                ->addColumn('products', Table::TYPE_TEXT, null, ['nullable' => false])
                ->addColumn('server_restriction', Table::TYPE_TEXT, null, ['nullable' => false])
                ->addColumn('server_restriction1', Table::TYPE_TEXT, null, ['nullable' => true])
                ->addColumn('server_restriction2', Table::TYPE_TEXT, null, ['nullable' => true])
                ->addColumn('license_expire', Table::TYPE_DATETIME, null, ['nullable' => true, 'default' => null])
                ->addColumn('upgrade_expire', Table::TYPE_DATETIME, null, ['nullable' => true, 'default' => null])
                ->addColumn('signature', Table::TYPE_TEXT, null)
                ->addColumn('server_info', Table::TYPE_TEXT, null)
                ->addColumn('aux_checksum', Table::TYPE_INTEGER, null, ['unsigned' => true])
                ->setComment('Unirgy License Table')
                ->setOption('AUTO_INCREMENT', '8')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $connection->createTable($table);
        }
        $installer->endSetup();
    }
}
