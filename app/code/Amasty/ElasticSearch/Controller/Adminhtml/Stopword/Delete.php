<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Controller\Adminhtml\Stopword;

use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\App\Action;
use Amasty\ElasticSearch\Api\Data\StopWordInterface;

class Delete extends Action
{
    const ADMIN_RESOURCE = 'Amasty_ElasticSearch::stop_words';

    /**
     * @var \Amasty\ElasticSearch\Model\StopWordRepository
     */
    private $stopWordRepository;

    /**
     * @var \Amasty\ElasticSearch\Model\ResourceModel\StopWord\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Filter
     */
    private $filter;

    public function __construct(
        Action\Context $context,
        \Amasty\ElasticSearch\Model\StopWordRepository $stopWordRepository,
        \Amasty\ElasticSearch\Model\ResourceModel\StopWord\CollectionFactory $collectionFactory,
        Filter $filter
    ) {
        parent::__construct($context);

        $this->stopWordRepository = $stopWordRepository;
        $this->collectionFactory = $collectionFactory;
        $this->filter = $filter;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $idsToRemove = [];

        $ids = $this->getRequest()->getParam(StopWordInterface::STOP_WORD_ID);
        if ($ids) {
            $idsToRemove = [$ids];
        }

        if ($this->getRequest()->getParam(Filter::SELECTED_PARAM)
            || $this->getRequest()->getParam(Filter::EXCLUDED_PARAM)
        ) {
            $idsToRemove = $this->filter->getCollection($this->collectionFactory->create())->getAllIds();
        }
        if ($idsToRemove) {
            foreach ($idsToRemove as $id) {
                try {
                    $this->stopWordRepository->deleteById($id);
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
            }

            $this->messageManager->addSuccessMessage(
                __('%1 stop word(s) was successfully removed', count($idsToRemove))
            );
        } else {
            $this->messageManager->addErrorMessage(__('Please select Stop Word(s)'));
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
