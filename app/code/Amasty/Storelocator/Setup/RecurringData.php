<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Amasty\Storelocator\Model\ResourceModel\ConverterFactory;
use Magento\Framework\DB\FieldToConvert;

/**
 * Recurring Data script
 */
class RecurringData implements InstallDataInterface
{
    /**
     * @var ConverterFactory
     */
    private $converterFactory;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * UpgradeData constructor.
     *
     * @param ProductMetadataInterface $productMetadata
     * @param ConverterFactory         $converterFactory
     */
    public function __construct(
        ProductMetadataInterface $productMetadata,
        ConverterFactory $converterFactory
    ) {
        $this->productMetadata = $productMetadata;
        $this->converterFactory = $converterFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($this->productMetadata->getVersion(), '2.2', '>=')) {
            $this->convertSerializedDataToJson($setup);
        }
    }

    /**
     * Convert metadata from serialized to JSON format:
     *
     * @param ModuleDataSetupInterface $setup
     *
     * @return void
     */
    public function convertSerializedDataToJson($setup)
    {
        $aggregatedFieldConverter = $this->converterFactory->create();
        $aggregatedFieldConverter->convert(
            [
                new FieldToConvert(
                    'Magento\Framework\DB\DataConverter\SerializedToJson',
                    $setup->getTable('amasty_amlocator_location'),
                    'id',
                    'actions_serialized'
                ),
                new FieldToConvert(
                    'Magento\Framework\DB\DataConverter\SerializedToJson',
                    $setup->getTable('amasty_amlocator_attribute'),
                    'attribute_id',
                    'label_serialized'
                ),
                new FieldToConvert(
                    'Magento\Framework\DB\DataConverter\SerializedToJson',
                    $setup->getTable('amasty_amlocator_attribute_option'),
                    'value_id',
                    'options_serialized'
                )
            ],
            $setup->getConnection()
        );
    }
}
