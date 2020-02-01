<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Indexer\MultiThread;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class PostDimensionFactory
 *
 * @package Aheadworks\Blog\Model\Indexer\MultiThread
 */
class PostDimensionFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create post dimension instance
     *
     * @param string $name
     * @param array $value
     * @return PostDimension
     */
    public function create($name, $value)
    {
        return $this->objectManager->create(
            PostDimension::class,
            [
                'name' => $name,
                'value' => $value,
            ]
        );
    }
}
