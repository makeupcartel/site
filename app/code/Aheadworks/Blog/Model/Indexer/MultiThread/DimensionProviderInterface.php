<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Indexer\MultiThread;

/**
 * Interface DimensionProviderInterface
 *
 * It is created for compatibility with 2.1.X Magento
 *
 * @package Aheadworks\Blog\Model\Indexer\MultiThread
 */
interface DimensionProviderInterface extends \IteratorAggregate
{
    /**
     * Get Dimension Iterator.
     *
     * @return \Traversable
     */
    public function getIterator();
}
