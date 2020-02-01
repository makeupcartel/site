<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\ResourceModel\Indexer\ProductPost;

use Aheadworks\Blog\Model\ResourceModel\Indexer\ProductPost\DataProcessor\BatchingProcessor;
use Aheadworks\Blog\Model\ResourceModel\Indexer\ProductPost\DataProcessor\LegacyProcessor;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Aheadworks\Blog\Model\ResourceModel\Indexer\ProductPost\DataProcessor\DataProcessorInterface;

/**
 * Class DataProcessorFactory
 *
 * @package Aheadworks\Blog\Model\ResourceModel\Indexer\ProductPost
 */
class DataProcessorFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ProductMetadataInterface $productMetadata
    ) {
        $this->objectManager = $objectManager;
        $this->productMetadata = $productMetadata;
    }

    /**
     * Create data processor depending on Magento version
     *
     * @return DataProcessorInterface
     */
    public function create()
    {
        $magentoVersion = $this->productMetadata->getVersion();
        if (version_compare($magentoVersion, '2.2.0', '>=')) {
            $instance = $this->objectManager->create(BatchingProcessor::class);
        } else {
            $instance = $this->objectManager->create(LegacyProcessor::class);
        }

        return $instance;
    }
}
