<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Model\Indexer\Category;

use Amasty\Xsearch\Model\Indexer\Category\Fulltext\Action\FullFactory;
use \Magento\Framework\Search\Request\Config as SearchRequestConfig;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Search\Request\DimensionFactory;

class Fulltext implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    const INDEXER_ID = 'amasty_xsearch_category_fulltext';

    private $fullAction;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DimensionFactory
     */
    private $dimensionFactory;

    /**
     * @var IndexerHandlerFactory
     */
    private $indexerHandlerFactory;

    /**
     * @var SearchRequestConfig
     */
    private $searchRequestConfig;

    /**
     * @var array
     */
    private $data;

    /**
     * Fulltext constructor.
     * @param FullFactory $fullActionFactory
     * @param StoreManagerInterface $storeManager
     * @param DimensionFactory $dimensionFactory
     * @param IndexerHandlerFactory $indexerHandlerFactory
     * @param SearchRequestConfig $searchRequestConfig
     * @param array $data
     */
    public function __construct(
        FullFactory $fullActionFactory,
        StoreManagerInterface $storeManager,
        DimensionFactory $dimensionFactory,
        IndexerHandlerFactory $indexerHandlerFactory,
        SearchRequestConfig $searchRequestConfig,
        array $data
    ) {
        $this->fullAction = $fullActionFactory->create(['data' => $data]);
        $this->storeManager = $storeManager;
        $this->dimensionFactory = $dimensionFactory;
        $this->indexerHandlerFactory = $indexerHandlerFactory;
        $this->searchRequestConfig = $searchRequestConfig;
        $this->data = $data;
    }

    public function executeFull()
    {
        $storeIds = array_keys($this->storeManager->getStores());
        /** @var IndexerHandler $saveHandler */
        $saveHandler = $this->indexerHandlerFactory->create([
            'data' => $this->data
        ]);

        foreach ($storeIds as $storeId) {
            $dimension = $this->dimensionFactory->create(['name' => 'scope', 'value' => $storeId]);

            $saveHandler->cleanIndex([$dimension]);
            $saveHandler->saveIndex([$dimension], $this->fullAction->rebuildStoreIndex($storeId));

        }
        $this->searchRequestConfig->reset();
    }

    public function execute($ids)
    {
        $storeIds = array_keys($this->storeManager->getStores());
        $saveHandler = $this->indexerHandlerFactory->create([
            'data' => $this->data
        ]);

        foreach ($storeIds as $storeId) {
            $dimension = $this->dimensionFactory->create(['name' => 'scope', 'value' => $storeId]);
            $saveHandler->deleteIndex([$dimension], new \ArrayObject($ids));
            $saveHandler->saveIndex([$dimension], $this->fullAction->rebuildStoreIndex($storeId, $ids));
        }
    }

    public function executeList(array $ids)
    {
        $this->execute($ids);
    }

    public function executeRow($id)
    {
        $this->execute([$id]);
    }
}
