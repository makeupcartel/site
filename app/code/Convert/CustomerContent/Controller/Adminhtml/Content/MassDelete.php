<?php

namespace Convert\CustomerContent\Controller\Adminhtml\Content;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Controller\ResultFactory;
use Convert\CustomerContent\Controller\Adminhtml\AbstractMassAction;
use Convert\CustomerContent\Model\ResourceModel\Content\CollectionFactory;
use Convert\CustomerContent\Model\ContentFactory;

class MassDelete extends AbstractMassAction
{

    const ADMIN_RESOURCE = 'Convert_CustomerContent::menu_top_content';

    /**
     * @var \Convert\CustomerContent\Api\ContentRepositoryInterface
     */
    protected $ContentRepository;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param ContentFactory $manageFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ContentFactory $manageFactory
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->model = $manageFactory;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction($collection)
    {
        $itemsDeleted = 0;
        foreach ($collection as $item) {
            $model = $this->model->create()->load($item->getId());
            $model->delete();
            $itemsDeleted++;
        }

        if ($itemsDeleted) {
            $this->messageManager->addSuccess(__('A total of %1 Content(s) were deleted.', $itemsDeleted));
        } else {
            $this->messageManager->addErrorMessage('Something went wrong while delete Content');
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
