<?php

namespace Convert\CustomerContent\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;

class Edit extends Action
{
    const ADMIN_RESOURCE = 'Convert_CustomerContent::menu_top_category';
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Convert\CustomerContent\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Backend\Model\SessionFactory
     */
    protected $sessionFactory;

    /**
     * Edit constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\SessionFactory $sessionFactory
     * @param \Convert\CustomerContent\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\SessionFactory $sessionFactory,
        \Convert\CustomerContent\Model\CategoryFactory $categoryFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->sessionFactory = $sessionFactory;
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(
            'Convert_CustomerContent::menu_top_category'
        )->addBreadcrumb(
            __('Manage Categories'),
            __('Manage Categories')
        )->addBreadcrumb(
            __('Manage Categories'),
            __('Manage Categories')
        );
        return $resultPage;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('category_id');
        $model = $this->categoryFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This Category no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $data = $this->sessionFactory->create()->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('category', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Category') : __('New Category'),
            $id ? __('Edit Category') : __('New Category')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Category'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getTitle() : __('New Category'));
        return $resultPage;
    }
}
