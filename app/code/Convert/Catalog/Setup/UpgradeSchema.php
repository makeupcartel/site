<?php

namespace Convert\Catalog\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class UpgradeSchema
 *
 * @package Convert\Catalog\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '0.0.2', '<')) {
            $setup->getConnection()->dropColumn($setup->getTable('catalog_product_bundle_option'), 'max_option');
            $setup->getConnection()->addColumn(
                $setup->getTable('catalog_product_bundle_option'),
                'max_option',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Maximum number options selected',
                ]
            );
        }
    }
}
