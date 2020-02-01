<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Indexer\ProductPost\Action\Full;

use Aheadworks\Blog\Model\ResourceModel\Indexer\ProductPost as ResourceProductPostIndexer;

/**
 * Class SingleThreadProcessor
 *
 * @package Aheadworks\Blog\Model\Indexer\ProductPost\Action\Full
 */
class SingleThreadProcessor
{
    /**
     * @var ResourceProductPostIndexer
     */
    private $resourceProductPostIndexer;

    /**
     * @param ResourceProductPostIndexer $resourceProductPostIndexer
     */
    public function __construct(
        ResourceProductPostIndexer $resourceProductPostIndexer
    ) {
        $this->resourceProductPostIndexer = $resourceProductPostIndexer;
    }

    /**
     * Execute Full reindex in single thread mode (old way)
     *
     * @param array|int|null $ids
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute($ids = null)
    {
        try {
            $this->resourceProductPostIndexer->reindexAll();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }
}
