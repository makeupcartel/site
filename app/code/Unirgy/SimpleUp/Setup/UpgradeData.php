<?php

namespace Unirgy\SimpleUp\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Upgrade Data script
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            $setup->getConnection()->update(
                $setup->getTable('usimpleup_module'),
                ['download_uri'=>'https://download.unirgy.com/Unirgy_SimpleUp-latest.zip?m=2'],
                "module_name='Unirgy_SimpleUp'"
            );
        }
        $setup->endSetup();
    }
}
