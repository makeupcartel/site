<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Controller\Adminhtml\Synonym;

use Amasty\ElasticSearch\Api\Data\SynonymInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Save extends AbstractSynonym
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $stopWordId = (int)$this->getRequest()->getParam(SynonymInterface::SYNONYM_ID);

        try {
            if ($stopWordId) {
                /** @var  \Amasty\ElasticSearch\Model\Synonym $model */
                $model = $this->synonymRepository->getById($stopWordId);
            } else {
                $model = $this->synonymFactory->create();
            }

            $model->setTerm($this->getRequest()->getParam(SynonymInterface::TERM))
                ->setStoreId($this->getRequest()->getParam(SynonymInterface::STORE_ID));
            $this->synonymRepository->save($model);
            $this->messageManager->addSuccessMessage(__('You have saved the Synonym.'));
        } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
            $this->messageManager->addErrorMessage(
                __('A Synonym with the same term already exists in an associated store.')
            );
        } catch (NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage(__('This Synonym no longer exists.'));
            $resultRedirect = $this->resultRedirectFactory->create();
        }

        $matches = null;
        preg_match('/(\+|-|\/|&&|\|\||!|\(|\)|\{|}|\[|]|\^|"|~|\*|\?|:|\\\)/', $model->getTerm(), $matches);
        if (count($matches)) {
            $this->messageManager->addNoticeMessage(__(
                'Synonyms with special symbols only works on elasticsearch engine v6.0 or higher.'
            ));
        }

        return $resultRedirect->setPath('*/*/');
    }
}
