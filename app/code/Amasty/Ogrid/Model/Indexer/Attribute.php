<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */

namespace Amasty\Ogrid\Model\Indexer;

use Amasty\Ogrid\Model\Indexer\Attribute\Action\FullFactory;
use \Magento\Framework\Search\Request\Config as SearchRequestConfig;
use Magento\Framework\Search\Request\DimensionFactory;
use Magento\Store\Model\StoreManagerInterface;

class Attribute implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{

    const INDEXER_ID = 'amasty_ogrid_attribute_index';

    protected $data;

    private $indexerHandlerFactory;

    private $storeManager;

    private $dimensionFactory;

    private $fullAction;

    private $searchRequestConfig;

    public function __construct(
        FullFactory $fullActionFactory,
        IndexerHandlerFactory $indexerHandlerFactory,
        StoreManagerInterface $storeManager,
        DimensionFactory $dimensionFactory,
        SearchRequestConfig $searchRequestConfig,
        array $data
    ) {
        $this->fullAction = $fullActionFactory->create(['data' => $data]);
        $this->indexerHandlerFactory = $indexerHandlerFactory;
        $this->storeManager = $storeManager;
        $this->dimensionFactory = $dimensionFactory;
        $this->searchRequestConfig = $searchRequestConfig;
        $this->data = $data;
    }

    public function execute($ids)
    {
        $saveHandler = $this->indexerHandlerFactory->create([
            'data' => $this->data
        ]);

        $dimension = [];

        $attributes = $saveHandler->getIndexedAttributesHash($dimension);
        $saveHandler->setAttributeHash($attributes);

        $saveHandler->saveIndex(
            $dimension,
            $this->fullAction->rebuildIndex(
                $attributes,
                $ids
            )
        );
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        /** @var IndexerHandler $saveHandler */
        $saveHandler = $this->indexerHandlerFactory->create([
            'data' => $this->data
        ]);

        $dimension = [];

        $attributes = $saveHandler->getNoneIndexedAttributesHash();
        if (!$attributes) {
            return;
        }
        $saveHandler->setAttributeHash($attributes);

        $saveHandler->cleanIndex($dimension);
        $saveHandler->saveIndex(
            $dimension,
            $this->fullAction->rebuildIndex($attributes)
        );
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids)
    {
        $this->execute($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
        $this->execute([$id]);
    }
}
