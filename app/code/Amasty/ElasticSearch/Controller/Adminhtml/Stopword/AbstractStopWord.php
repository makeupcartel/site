<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Controller\Adminhtml\Stopword;

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;

abstract class AbstractStopWord extends Action
{
    const ADMIN_RESOURCE = 'Amasty_ElasticSearch::stop_words';

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Amasty\ElasticSearch\Model\StopWordRepository
     */
    protected $stopWordRepository;

    /**
     * @var \Amasty\ElasticSearch\Model\StopWordFactory
     */
    protected $stopWordFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Amasty\ElasticSearch\Model\StopWordRepository $ruleRepository,
        \Amasty\ElasticSearch\Model\StopWordFactory $stopWordFactory,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->stopWordRepository = $ruleRepository;
        $this->stopWordFactory = $stopWordFactory;
        $this->registry = $registry;
    }

    /**
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Amasty_ElasticSearch::Amasty_ElasticSearch');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Stop Words'));

        return $resultPage;
    }
}
