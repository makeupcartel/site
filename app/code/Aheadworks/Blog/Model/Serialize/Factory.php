<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Serialize;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * Class Factory
 * @package Aheadworks\Blog\Model\Serialize
 */
class Factory
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
     * Create serializer instance
     *
     * @return SerializeInterface
     */
    public function create()
    {
        $magentoVersion = $this->productMetadata->getVersion();
        $serializerInterface = version_compare($magentoVersion, '2.2.0', '>=')
            ? \Magento\Framework\Serialize\SerializerInterface::class
            : SerializeInterface::class;

        return $this->objectManager->create($serializerInterface);
    }
}
