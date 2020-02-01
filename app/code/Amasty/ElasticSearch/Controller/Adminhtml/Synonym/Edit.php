<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Controller\Adminhtml\Synonym;

use Magento\Framework\Controller\ResultFactory;
use Amasty\ElasticSearch\Api\Data\SynonymInterface;

class Edit extends AbstractSynonym
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $stopWordId = (int)$this->getRequest()->getParam(SynonymInterface::SYNONYM_ID);

        try {
            if ($stopWordId) {
                /** @var  \Amasty\ElasticSearch\Model\Synonym $model */
                $model = $this->synonymRepository->getById($stopWordId);
            } else {
                $model = $this->synonymFactory->create();
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage(__('This Synonym no longer exists.'));
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setPath('*/*/');
        }

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $text = $model->getId() ? __('Edit Synonym "%1"', $model->getTerm()) : __('New Synonym');
        $this->initPage($resultPage)->getConfig()->getTitle()->prepend($text);

        return $resultPage;
    }
}
