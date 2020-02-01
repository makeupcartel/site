<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Cron;

class RelevanceRuleDailyUpdate
{
    /**
     * @var \Amasty\ElasticSearch\Model\Indexer\RelevanceRule\RuleProductProcessor
     */
    protected $ruleProductProcessor;

    /**
     * @param \Amasty\ElasticSearch\Model\Indexer\RelevanceRule\RuleProductProcessor $ruleProductProcessor
     */
    public function __construct(\Amasty\ElasticSearch\Model\Indexer\RelevanceRule\RuleProductProcessor $ruleProductProcessor)
    {
        $this->ruleProductProcessor = $ruleProductProcessor;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $this->ruleProductProcessor->markIndexerAsInvalid();
    }
}
