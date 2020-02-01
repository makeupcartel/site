<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Controller\Adminhtml\RelevanceRule;

use Amasty\ElasticSearch\Api\Data\RelevanceRuleInterface;
use Magento\Framework\Controller\ResultFactory;

class Edit extends AbstractRelevance
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        $ruleId = (int)$this->getRequest()->getParam(RelevanceRuleInterface::RULE_ID);
        try {
            if ($ruleId) {
                /** @var  \Amasty\ElasticSearch\Model\RelevanceRule $model */
                $model = $this->ruleRepository->get($ruleId);
            } else {
                $model = $this->ruleFactory->create();
            }

            $this->registry->register(self::CURRENT_RULE, $model);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('This Relevance Rule is no longer exists.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/');
        }

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $text = $model->getId() ? $model->getTitle() : __('New Relevance Rule');
        $this->initPage($resultPage)->getConfig()->getTitle()->prepend($text);
        return $resultPage;
    }
}
