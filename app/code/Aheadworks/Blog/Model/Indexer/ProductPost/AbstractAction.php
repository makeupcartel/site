<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Indexer\ProductPost;

use Aheadworks\Blog\Model\ResourceModel\Indexer\ProductPost as ResourceProductPostIndexer;

/**
 * Class AbstractAction
 * @package Aheadworks\Blog\Model\Indexer\ProductPost
 */
abstract class AbstractAction
{
    /**
     * @var ResourceProductPostIndexer
     */
    protected $resourceProductPostIndexer;

    /**
     * @param ResourceProductPostIndexer $resourceProductPostIndexer
     */
    public function __construct(
        ResourceProductPostIndexer $resourceProductPostIndexer
    ) {
        $this->resourceProductPostIndexer = $resourceProductPostIndexer;
    }

    /**
     * Execute action for given ids
     *
     * @param array|int $ids
     * @return void
     */
    abstract public function execute($ids);
}
