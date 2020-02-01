<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */


namespace Amasty\Ogrid\Model\Indexer\Mview;

use Amasty\Ogrid\Model\Indexer\Attribute;
use Magento\Framework\Mview\ActionInterface;
use Magento\Framework\Indexer\IndexerInterfaceFactory;

class Action implements ActionInterface
{
    /**
     * @var IndexerInterfaceFactory
     */
    private $indexerFactory;

    /**
     * @param IndexerInterfaceFactory $indexerFactory
     */
    public function __construct(IndexerInterfaceFactory $indexerFactory)
    {
        $this->indexerFactory = $indexerFactory;
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     * @api
     */
    public function execute($ids)
    {
        /** @var \Magento\Framework\Indexer\IndexerInterface $indexer */
        $indexer = $this->indexerFactory->create()->load(Attribute::INDEXER_ID);
        $indexer->reindexList($ids);
    }
}
