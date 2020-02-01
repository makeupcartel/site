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

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 *
 * @package BitExpert\ForceCustomerLogin\Setup
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.1.0', '<')) {
            $this->runUpgrade210($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function runUpgrade210(SchemaSetupInterface $setup)
    {
        $installer = $setup;

        $installer->startSetup();

        // add strategy column
        $installer->getConnection()
            ->addColumn(
                $installer->getTable('bitexpert_forcelogin_whitelist'),
                'strategy',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'size' => 255,
                    'nullable' => true,
                    'comment' => 'strategy matcher identifier',
                ]
            );

        $installer->endSetup();
    }
}
