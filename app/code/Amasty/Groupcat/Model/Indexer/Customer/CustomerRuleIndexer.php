<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

namespace Amasty\Groupcat\Model\Indexer\Customer;

use Amasty\Groupcat\Model\Indexer\AbstractIndexer;

class CustomerRuleIndexer extends AbstractIndexer
{
    /**
     * Override constructor. Indexer is changed
     *
     * @param IndexBuilder                              $indexBuilder
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Amasty\Groupcat\Model\Indexer\Customer\IndexBuilder $indexBuilder,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        parent::__construct($indexBuilder, $eventManager);
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecuteList($ids)
    {
        $this->indexBuilder->reindexByCustomerIds(array_unique($ids));
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecuteRow($id)
    {
        $this->indexBuilder->reindexByCustomerId($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        return [
            \Magento\Framework\App\Cache\Type\Block::CACHE_TAG
        ];
    }
}
