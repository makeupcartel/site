<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Controller\Adminhtml\Stopword;

use Magento\Framework\Controller\ResultFactory;
use Amasty\ElasticSearch\Api\Data\StopWordInterface;

class Edit extends AbstractStopWord
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $stopWordId = (int)$this->getRequest()->getParam(StopWordInterface::STOP_WORD_ID);

        try {
            if ($stopWordId) {
                /** @var  \Amasty\ElasticSearch\Model\StopWord $model */
                $model = $this->stopWordRepository->getById($stopWordId);
            } else {
                $model = $this->stopWordFactory->create();
            }

        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage(__('This Stop Word no longer exists.'));
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setPath('*/*/');
        }

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $text = $model->getId() ? __('Edit Stop Word "%1"', $model->getTerm()) : __('New Stop Word');
        $this->initPage($resultPage)->getConfig()->getTitle()->prepend($text);

        return $resultPage;
    }
}
