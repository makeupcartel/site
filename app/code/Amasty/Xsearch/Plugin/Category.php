<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Plugin;

use Amasty\Xsearch\Model\Indexer\Category\Fulltext;
use Magento\Framework\Indexer\IndexerRegistry;

class Category
{
    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    public function __construct(
        IndexerRegistry $indexerRegistry
    ) {
        $this->indexerRegistry = $indexerRegistry;
    }

    public function afterReindex(\Magento\Catalog\Model\Category $category, $result)
    {
        $indexer = $this->indexerRegistry->get(Fulltext::INDEXER_ID);
        if (!$indexer->isScheduled()) {
            $indexer->reindexList($category->getPathIds());
        }

        return $result;
    }
}
