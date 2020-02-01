<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2018-07-12T14:03:04+00:00
 * File:          app/code/Xtento/ProductExport/Setup/RecurringData.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class RecurringData
 * @package Xtento\ProductExport\Setup
 */
class RecurringData implements InstallDataInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Xtento\XtCore\Helper\Utils
     */
    protected $utilsHelper;

    /**
     * RecurringData constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Xtento\XtCore\Helper\Utils $utilsHelper
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Xtento\XtCore\Helper\Utils $utilsHelper
    ) {
        $this->objectManager = $objectManager;
        $this->utilsHelper = $utilsHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($this->utilsHelper->getMagentoVersion(), '2.2', '>=')) {
            $this->convertSerializedDataToJson($setup);
        }
    }

    /**
     * Convert data from serialized to JSON format.
     *
     * Must be done on every call to setup:upgrade, as eventually someone who has the latest module version of this
     * module installed but upgrades from Magento 2.1 to 2.2, and then serialized data must be converted to JSON.
     *
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     *
     * @return void
     */
    private function convertSerializedDataToJson(\Magento\Framework\Setup\ModuleDataSetupInterface $setup)
    {
        /** @var \Magento\Framework\DB\FieldDataConverterFactory $fieldDataConverterFactory */
        $fieldDataConverterFactory = $this->objectManager->create('\Magento\Framework\DB\FieldDataConverterFactory');
        /** @var \Magento\Framework\DB\FieldDataConverter $fieldDataConverter */
        $fieldDataConverter = $fieldDataConverterFactory->create('\Xtento\ProductExport\Test\SerializedToJsonDataConverter');
        $fieldsToConvert = ['conditions_serialized'];
        foreach ($fieldsToConvert as $fieldName) {
            $fieldDataConverter->convert(
                $setup->getConnection(),
                $setup->getTable('xtento_productexport_profile'),
                'profile_id',
                $fieldName
            );
        }
    }
}