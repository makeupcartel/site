<?php

namespace Convert\CustomerContent\Controller\Adminhtml\Category;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Controller\ResultFactory;
use Convert\CustomerContent\Controller\Adminhtml\AbstractMassAction;
use Convert\CustomerContent\Model\ResourceModel\Category\CollectionFactory;
use Convert\CustomerContent\Model\CategoryFactory;

class MassDelete extends AbstractMassAction
{

    const ADMIN_RESOURCE = 'Convert_CustomerContent::menu_top_category';

    /**
     * @var \Convert\CustomerContent\Api\CategoryRepositoryInterface
     */
    protected $CategoryRepository;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param CategoryFactory $manageFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        CategoryFactory $manageFactory
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
            $this->messageManager->addSuccess(__('A total of %1 Category(s) were deleted.', $itemsDeleted));
        } else {
            $this->messageManager->addErrorMessage('Something went wrong while delete Category');
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
