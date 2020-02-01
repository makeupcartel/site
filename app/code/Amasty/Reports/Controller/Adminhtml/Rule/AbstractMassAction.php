<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Controller\Adminhtml\Rule;

use Amasty\Reports\Api\Data\RuleInterface;
use Amasty\Reports\Api\RuleRepositoryInterface;
use Amasty\Reports\Controller\Adminhtml\Rule as RuleController;
use Amasty\Reports\Model\ResourceModel\Rule\Collection as RuleCollection;
use Amasty\Reports\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractMassAction
 * @package Amasty\Reports\Controller\Adminhtml\Rule
 */
abstract class AbstractMassAction extends RuleController
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var RuleRepositoryInterface
     */
    protected $repository;

    /**
     * @var RuleCollectionFactory
     */
    protected $ruleCollectionFactory;

    public function __construct(
        Action\Context $context,
        Filter $filter,
        LoggerInterface $logger,
        RuleRepositoryInterface $repository,
        RuleCollectionFactory $ruleCollectionFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->logger = $logger;
        $this->repository = $repository;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
    }

    /**
     * Execute action for group
     *
     * @param RuleInterface $rule
     */
    abstract protected function itemAction(RuleInterface $rule);

    /**
     * Mass action execution
     */
    public function execute()
    {
        $this->filter->applySelectionOnTargetProvider(); // compatibility with Mass Actions on Magento 2.1.0
        /** @var RuleCollection $collection */
        $collection = $this->filter->getCollection($this->ruleCollectionFactory->create());

        $collectionSize = $collection->getSize();
        if ($collectionSize) {
            try {
                /** @var RuleInterface $model */
                foreach ($collection->getItems() as $model) {
                    $this->itemAction($model);
                }

                $this->messageManager->addSuccessMessage($this->getSuccessMessage($collectionSize));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($this->getErrorMessage());
                $this->logger->critical($e);
            }
        }

        $this->_redirect($this->_redirect->getRefererUrl());
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    protected function getErrorMessage()
    {
        return __('We can\'t change item right now. Please review the log and try again.');
    }

    /**
     * @param int $collectionSize
     *
     * @return \Magento\Framework\Phrase
     */
    protected function getSuccessMessage($collectionSize = 0)
    {
        if ($collectionSize) {
            return __('A total of %1 record(s) have been changed.', $collectionSize);
        }

        return __('No records have been changed.');
    }
}
