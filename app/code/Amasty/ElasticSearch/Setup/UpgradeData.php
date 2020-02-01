<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Setup;

use Amasty\ElasticSearch\Model\Indexer\RelevanceRule\ProductRuleProcessor;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Indexer\Model\IndexerFactory;
use Magento\Framework\App\State;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var IndexerFactory
     */
    private $indexer;

    /**
     * @var State
     */
    private $state;

    public function __construct(
        IndexerFactory $indexer,
        State $state
    ) {
        $this->indexer = $indexer;
        $this->state = $state;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        if (version_compare($context->getVersion(), '1.3.5', '<')) {
            $this->state->emulateAreaCode(
                'adminhtml',
                [$this, 'reindexAll']
            );
        }
    }

    public function reindexAll()
    {
        $indexer = $this->indexer->create()->load(ProductRuleProcessor::INDEXER_ID);
        $indexer->reindexAll();
    }
}
