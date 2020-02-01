<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

namespace Amasty\Groupcat\Controller\Adminhtml;

/**
 * Items controller
 */
abstract class Rule extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_Groupcat::rule';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Amasty\Groupcat\Api\RuleRepositoryInterface
     */
    protected $ruleRepository;

    /**
     * @var \Amasty\Groupcat\Model\RuleFactory
     */
    protected $ruleFactory;

    /**
     * Rule constructor.
     *
     * @param \Magento\Backend\App\Action\Context          $context
     * @param \Magento\Framework\Registry                  $coreRegistry
     * @param \Amasty\Groupcat\Api\RuleRepositoryInterface $ruleRepository
     * @param \Amasty\Groupcat\Model\RuleFactory           $ruleFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Amasty\Groupcat\Api\RuleRepositoryInterface $ruleRepository,
        \Amasty\Groupcat\Model\RuleFactory $ruleFactory
    ) {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context);
        $this->ruleRepository = $ruleRepository;
        $this->ruleFactory    = $ruleFactory;
    }

    /**
     * Initiate action
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(self::ADMIN_RESOURCE)
            ->_addBreadcrumb(__('Customer Group Catalog Rules'), __('Customer Group Catalog Rules'));

        return $this;
    }
}
