<?php
/**
 * Created by pp
 *
 * @project magento2
 */

namespace Unirgy\RapidFlow\Setup;

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
        $tableName = $installer->getTable('urapidflow_profile');

        $connection = $installer->getConnection();
        if ($connection->isTableExists($tableName) != true) {
            $table = $connection->newTable($tableName)
                ->addColumn('profile_id', Table::TYPE_INTEGER, 10, [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ], 'Profile Id')
                ->addColumn('title', Table::TYPE_TEXT, 255, ['nullable' => false])
                ->addColumn('profile_type', Table::TYPE_TEXT, 20, ['nullable' => false, 'default' => 'import'])
                ->addColumn('profile_status', Table::TYPE_TEXT, 20, ['nullable' => false, 'default' => 'disabled'])
                ->addColumn('media_type', Table::TYPE_TEXT, 20, ['nullable' => false, 'default' => 'csv'])
                ->addColumn('run_status', Table::TYPE_TEXT, 20, ['nullable' => false, 'default' => 'idle'])
                ->addColumn('invoke_status', Table::TYPE_TEXT, 20, ['nullable' => false, 'default' => 'none'])
                ->addColumn('data_type', Table::TYPE_TEXT, 255, ['nullable' => false])
                ->addColumn('base_dir', Table::TYPE_TEXT, null, ['nullable' => false])
                ->addColumn('filename', Table::TYPE_TEXT, 255, ['nullable' => false])
                ->addColumn('store_id', Table::TYPE_SMALLINT, 5, ['unsigned' => true, 'nullable' => false])
                ->addColumn('rows_found', Table::TYPE_INTEGER, 10, ['unsigned' => true, 'nullable' => false])
                ->addColumn('rows_processed', Table::TYPE_INTEGER, 10, ['unsigned' => true, 'nullable' => false])
                ->addColumn('rows_success', Table::TYPE_INTEGER, 10, ['unsigned' => true, 'nullable' => false])
                ->addColumn('rows_nochange', Table::TYPE_INTEGER, 10, ['unsigned' => true, 'nullable' => false])
                ->addColumn('rows_empty', Table::TYPE_INTEGER, 10, ['unsigned' => true, 'nullable' => false])
                ->addColumn('rows_depends', Table::TYPE_INTEGER, 10, ['unsigned' => true, 'nullable' => false])
                ->addColumn('rows_errors', Table::TYPE_INTEGER, 10, ['unsigned' => true, 'nullable' => false])
                ->addColumn('num_errors', Table::TYPE_INTEGER, 10, ['unsigned' => true, 'nullable' => false])
                ->addColumn('num_warnings', Table::TYPE_INTEGER, 10, ['unsigned' => true, 'nullable' => false])
                ->addColumn('scheduled_at', Table::TYPE_DATETIME, null, ['default' => null])
                ->addColumn('started_at', Table::TYPE_DATETIME, null, ['default' => null])
                ->addColumn('snapshot_at', Table::TYPE_DATETIME, null, ['default' => null])
                ->addColumn('paused_at', Table::TYPE_DATETIME, null, ['default' => null])
                ->addColumn('stopped_at', Table::TYPE_DATETIME, null, ['default' => null])
                ->addColumn('finished_at', Table::TYPE_DATETIME, null, ['default' => null])
                ->addColumn('time_elapsed', Table::TYPE_INTEGER, 10, ['unsigned' => true, 'nullable' => false])
                ->addColumn('last_user_id', Table::TYPE_INTEGER, 9, ['unsigned' => true, 'default' => null])
                ->addColumn('columns_json', Table::TYPE_TEXT)
                ->addColumn('options_json', Table::TYPE_TEXT)
                ->addColumn('conditions_json', Table::TYPE_TEXT)
                ->addColumn('current_activity', Table::TYPE_TEXT, 100)
                ->addColumn('profile_state_json', Table::TYPE_TEXT)
                ->addColumn('memory_usage', Table::TYPE_INTEGER, 10, ['unsigned' => true, 'default' => null])
                ->addColumn('memory_peak_usage', Table::TYPE_INTEGER, 10, ['unsigned' => true, 'default' => null])
                ->setComment('RapidFlow Profiles Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $connection->createTable($table);
        }
        $installer->endSetup();
    }
}

