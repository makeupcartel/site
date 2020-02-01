<?php
/**
 * Created by pp
 *
 * @project magento2
 */

namespace Unirgy\SimpleLicense\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    )
    {
        $setup->startSetup();

        $tableName = $setup->getTable('usimpleup_module');
        // Check if the table already exists
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            // Declare data
            $data = [
                [
                    "module_name"           => 'Unirgy_SimpleLicense',
                    "download_uri"         => 'https://download.unirgy.com/Unirgy_SimpleLicense-latest.zip?m=2',
                ],
            ];
            // Insert data to table
            foreach ($data as $item) {
                $setup->getConnection()->insert($tableName, $item);
            }
        }

        $setup->endSetup();
    }
}
