<?php
/**
 * Created by pp
 *
 * @project magento2
 */

namespace Unirgy\SimpleUp\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{

    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $tableName = $installer->getTable('usimpleup_module');

        $connection = $installer->getConnection();
        if ($connection->isTableExists($tableName) != true) {
            $table = $connection->newTable($tableName)
                ->addColumn('module_id', Table::TYPE_INTEGER, 10, [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ], 'Module Id')
                ->addColumn('module_name', Table::TYPE_TEXT, 255, ['nullable' => false, 'default'=>''])
                ->addColumn('download_uri', Table::TYPE_TEXT, null, ['nullable' => false])
                ->addColumn('last_checked', Table::TYPE_DATETIME, null, ['nullable' => true, 'default' => null])
                ->addColumn('last_downloaded', Table::TYPE_DATETIME, null, ['nullable' => true, 'default' => null])
                ->addColumn('last_stability', Table::TYPE_TEXT, 30, ['nullable' => true, 'default' => null])
                ->addColumn('last_version', Table::TYPE_TEXT, 30, ['nullable' => true, 'default' => null])
                ->addColumn('remote_version', Table::TYPE_TEXT, 30, ['nullable' => true, 'default' => null])
                ->addColumn('license_key', Table::TYPE_TEXT, 255, ['nullable' => true, 'default' => null])
                ->setComment('Unirgy Modules Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $connection->createTable($table);
        }
        $installer->endSetup();
    }
}
