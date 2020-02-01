<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Controller\Adminhtml\RelevanceRule;

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;

abstract class AbstractRelevance extends Action
{
    const ADMIN_RESOURCE = 'Amasty_ElasticSearch::relevance_rules';
    const CURRENT_RULE = 'amasty_elastic_relevance_rule';

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Amasty\ElasticSearch\Model\RelevanceRuleRepository
     */
    protected $ruleRepository;

    /**
     * @var \Amasty\ElasticSearch\Model\RelevanceRuleFactory
     */
    protected $ruleFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Amasty\ElasticSearch\Model\RelevanceRuleRepository $ruleRepository,
        \Amasty\ElasticSearch\Model\RelevanceRuleFactory $ruleFactory,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->ruleRepository = $ruleRepository;
        $this->ruleFactory = $ruleFactory;
        $this->registry = $registry;
    }

    /**
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Amasty_ElasticSearch::Amasty_ElasticSearch');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Relevance Rules'));
        return $resultPage;
    }
}
