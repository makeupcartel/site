<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amasty\Xsearch\Model\Indexer\Category\Mview;

use Amasty\Xsearch\Model\Indexer\Category\Fulltext;
use Magento\Framework\Mview\ActionInterface;
use Magento\Framework\Indexer\IndexerInterfaceFactory;

class Action implements ActionInterface
{
    /**
     * @var \Magento\Framework\Indexer\IndexerInterfaceFactory
     */
    private $indexerFactory;

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
        $indexer = $this->indexerFactory->create()->load(Fulltext::INDEXER_ID);
        $indexer->reindexList($ids);
    }
}
