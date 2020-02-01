<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Indexer\ProductPost\Action\Full;

use Aheadworks\Blog\Model\ResourceModel\Indexer\ProductPost as ResourceProductPostIndexer;
use Aheadworks\Blog\Model\Indexer\MultiThread\PostDimensionProvider;
use Magento\Framework\ObjectManagerInterface;
use Aheadworks\Blog\Model\Indexer\MultiThread\PostDimension;
use Aheadworks\Blog\Model\Indexer\ProductPost\TableMaintainer;
use Aheadworks\Blog\Model\ResourceModel\Indexer\ProductPost;

/**
 * Class MultiThreadProcessor
 *
 * @package Aheadworks\Blog\Model\Indexer\ProductPost\Action\Full
 */
class MultiThreadProcessor
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var PostDimensionProvider
     */
    private $dimensionProvider;

    /**
     * @var ResourceProductPostIndexer
     */
    private $resourceProductPostIndexer;

    /**
     * @var TableMaintainer
     */
    private $tableMaintainer;

    /**
     * @param ProductPost $resourceProductPostIndexer
     * @param PostDimensionProvider $dimensionProvider
     * @param ObjectManagerInterface $objectManager
     * @param TableMaintainer $tableMaintainer
     */
    public function __construct(
        ResourceProductPostIndexer $resourceProductPostIndexer,
        PostDimensionProvider $dimensionProvider,
        ObjectManagerInterface $objectManager,
        TableMaintainer $tableMaintainer
    ) {
        $this->resourceProductPostIndexer = $resourceProductPostIndexer;
        $this->dimensionProvider = $dimensionProvider;
        $this->objectManager = $objectManager;
        $this->tableMaintainer = $tableMaintainer;
    }

    /**
     * Execute Full reindex in multi thread mode since M2.2.6
     *
     * @param array|int|null $ids
     * @return void
     * @throws \Exception
     */
    public function execute($ids = null)
    {
        $this->tableMaintainer->clearTemporaryIndexTable();

        $processManager = $this->objectManager->create(\Magento\Indexer\Model\ProcessManager::class);
        $userFunctions = [];
        foreach ($this->dimensionProvider->getIterator() as $dimension) {
            $userFunctions[] = function () use ($dimension) {
                $this->reindexByDimension($dimension);
            };
        }
        $processManager->execute($userFunctions);

        $this->resourceProductPostIndexer->syncData();
    }

    /**
     * Reindex by dimension
     *
     * @param PostDimension $dimension
     * @throws \Exception
     */
    private function reindexByDimension($dimension)
    {
        $table = $this->tableMaintainer->createTableForDimension($dimension);
        $this->resourceProductPostIndexer->reindexDimension($dimension, $table);
        $this->tableMaintainer->dropTableForDimension($dimension);
    }
}
