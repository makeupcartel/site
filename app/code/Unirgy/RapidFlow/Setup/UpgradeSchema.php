<?php
/**
 * Created by pp
 *
 * @project magento2
 */

namespace Unirgy\RapidFlow\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $tableName = $installer->getTable(\Unirgy\RapidFlow\Model\ResourceModel\AbstractResource::TABLE_CATALOG_PRODUCT_ENTITY_MEDIA_GALLERY);

        $connection = $installer->getConnection();

        $indexList = $connection->getIndexList($tableName);

        if(!in_array('CATALOG_PRODUCT_ENTITY_MEDIA_GALLERY_VALUE', $indexList)) {
            $connection->addIndex($tableName, 'CATALOG_PRODUCT_ENTITY_MEDIA_GALLERY_VALUE', 'value');
        }
    }
}

