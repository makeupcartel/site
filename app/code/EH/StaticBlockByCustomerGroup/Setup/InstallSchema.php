<?php
/**
 * @author ExtensionHut Team
 * @copyright Copyright (c) 2019 ExtensionHut (https://www.extensionhut.com/)
 * @package EH_StaticBlockByCustomerGroup
 */

namespace EH\StaticBlockByCustomerGroup\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $installer->getConnection()
            ->addColumn($installer->getTable('cms_block'), 'customer_group_ids', array(
                'type'      => Table::TYPE_TEXT,
                'nullable'  => true,
                'comment'   => 'Customer Group IDs',
            ));

        $installer->endSetup();
    }
}
