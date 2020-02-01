<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Amasty\Ogrid\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    protected $_eavConfig;
    protected $_productAttributesIndexerProcessor;

    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Amasty\Ogrid\Model\Indexer\Attribute\Processor $_productAttributesIndexerProcessor
    ){
        $this->_eavConfig = $eavConfig;
        $this->_productAttributesIndexerProcessor = $_productAttributesIndexerProcessor;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $columns = ['attribute_id', 'attribute_code', 'frontend_label'];

        $entityTypeId = $this->_eavConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getEntityTypeId();

        $select = $setup->getConnection()->select()->from(
            $setup->getTable('eav_attribute'),
            []
        )->where(
            'entity_type_id = ?',
            $entityTypeId
        )->where(
            'attribute_code in (?)',
            ['thumbnail', 'description', 'activity', 'category_gear']
        )->columns($columns);


        $query = $setup->getConnection()
            ->insertFromSelect($select, $setup->getTable('amasty_ogrid_attribute'), $columns);

        $setup->getConnection()->query($query);

        $this->_productAttributesIndexerProcessor->markIndexerAsInvalid();

    }
}