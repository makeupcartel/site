<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

namespace Amasty\Groupcat\Model\Indexer\Rule;

use Amasty\Groupcat\Model\Indexer\AbstractIndexer;

class RuleIndexer extends AbstractIndexer
{
    /**
     * @var \Amasty\Groupcat\Model\Indexer\Customer\IndexBuilder
     */
    protected $customerIndexBuilder;

    public function __construct(
        \Amasty\Groupcat\Model\Indexer\Product\IndexBuilder $productIndexBuilder,
        \Amasty\Groupcat\Model\Indexer\Customer\IndexBuilder $customerIndexBuilder,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        parent::__construct($productIndexBuilder, $eventManager);
        $this->customerIndexBuilder = $customerIndexBuilder;
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecuteList($ids)
    {
        $this->indexBuilder->reindexByIds($ids);
        $this->customerIndexBuilder->reindexByIds($ids);
        $this->getCacheContext()->registerTags($this->getIdentities());
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecuteRow($id)
    {
        $this->indexBuilder->reindexById($id);
        $this->customerIndexBuilder->reindexById($id);
    }

    /**
     * {@inheritdoc}
     */
    public function executeFull()
    {
        $this->customerIndexBuilder->reindexFull();
        parent::executeFull();
    }
}
