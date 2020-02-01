<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Controller\Adminhtml\Stopword;

use Amasty\ElasticSearch\Api\Data\StopWordInterface;

class Save extends AbstractStopWord
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $stopWordId = (int)$this->getRequest()->getParam(StopWordInterface::STOP_WORD_ID);

        try {
            if ($stopWordId) {
                /** @var  \Amasty\ElasticSearch\Model\StopWord $model */
                $model = $this->stopWordRepository->getById($stopWordId);
            } else {
                $model = $this->stopWordFactory->create();
            }

            $model->setTerm($this->getRequest()->getParam(StopWordInterface::TERM))
                ->setStoreId($this->getRequest()->getParam(StopWordInterface::STORE_ID));
            $this->stopWordRepository->save($model);
            $this->messageManager->addSuccessMessage(__('You have saved the Stop Word.'));
        } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
            $this->messageManager->addErrorMessage(
                __('A Stop Word with the same term already exists in an associated store.')
            );
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage(__('This Stop Word no longer exists.'));
            $resultRedirect = $this->resultRedirectFactory->create();
        }

        return $resultRedirect->setPath('*/*/');
    }
}
