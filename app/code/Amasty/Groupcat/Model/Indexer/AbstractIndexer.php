<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

namespace Amasty\Groupcat\Model\Indexer;

abstract class AbstractIndexer extends \Magento\CatalogRule\Model\Indexer\AbstractIndexer
{
    /**
     * @param IndexBuilder $indexBuilder
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Amasty\Groupcat\Model\Indexer\AbstractIndexBuilder $indexBuilder,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->indexBuilder = $indexBuilder;
        $this->_eventManager = $eventManager;
    }
}
