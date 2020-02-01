<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\App\ProductMetadataInterface;
use Amasty\Groupcat\Api\Data\RuleInterface;
use Amasty\Groupcat\Model\ResourceModel\ConverterFactory;
use Magento\Framework\DB\FieldToConvert;

/**
 * Recurring Data script
 */
class RecurringData implements InstallDataInterface
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var ConverterFactory
     */
    private $converterFactory;

    /**
     * UpgradeData constructor.
     *
     * @param MetadataPool $metadataPool
     * @param ProductMetadataInterface $productMetadata
     * @param ConverterFactory $converterFactory
     */
    public function __construct(
        MetadataPool $metadataPool,
        ProductMetadataInterface $productMetadata,
        ConverterFactory $converterFactory
    ) {
        $this->productMetadata = $productMetadata;
        $this->metadataPool = $metadataPool;
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
        $metadata = $this->metadataPool->getMetadata(RuleInterface::class);
        $aggregatedFieldConverter = $this->converterFactory->create();
        $aggregatedFieldConverter->convert(
            [
                new FieldToConvert(
                    'Magento\Framework\DB\DataConverter\SerializedToJson',
                    $setup->getTable('amasty_groupcat_rule'),
                    $metadata->getLinkField(),
                    'conditions_serialized'
                ),
                new FieldToConvert(
                    'Magento\Framework\DB\DataConverter\SerializedToJson',
                    $setup->getTable('amasty_groupcat_rule'),
                    $metadata->getLinkField(),
                    'actions_serialized'
                ),
            ],
            $setup->getConnection()
        );
    }
}
