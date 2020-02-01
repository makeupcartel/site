<?php

namespace Convert\CustomerContent\Controller\Adminhtml\Content;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Controller\ResultFactory;
use Convert\CustomerContent\Controller\Adminhtml\AbstractMassAction;
use Convert\CustomerContent\Model\ResourceModel\Content\CollectionFactory;
use Convert\CustomerContent\Model\ContentFactory;

class MassStatus extends AbstractMassAction
{
    const ADMIN_RESOURCE = 'Thienminh_Marketplace::marketplace_Content';

    /**
     * @var \Magento\Framework\Url
     */
    protected $urlBuilder;

    /**
     * MassStatus constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param ContentFactory $manageFactory
     * @param \Magento\Framework\Url $urlBuilder
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ContentFactory $manageFactory,
        \Magento\Framework\Url $urlBuilder
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->model = $manageFactory;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param $collection
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function massAction($collection)
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $status = (int)$this->getRequest()->getParam('status');

        $itemsSelected = 0;
        foreach ($collection->getAllIds() as $itemId) {
            $model = $this->model->create()->load($itemId);
            $model->setStatus($status);
            $model->save();
            $itemsSelected++;
        }

        if ($itemsSelected) {
            $this->messageManager->addSuccess(__('A total of %1 Content(s) were updated.', $itemsSelected));
        } else {
            $this->messageManager->addErrorMessage('Something went wrong while update Content');
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}
