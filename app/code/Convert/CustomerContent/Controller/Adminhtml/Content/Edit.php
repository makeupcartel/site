<?php

namespace Convert\CustomerContent\Controller\Adminhtml\Content;

use Magento\Backend\App\Action;

class Edit extends Action
{
    const ADMIN_RESOURCE = 'Convert_CustomerContent::menu_top_content';
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
     * @var \Convert\CustomerContent\Model\ContentFactory
     */
    protected $contentFactory;

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
     * @param \Convert\CustomerContent\Model\ContentFactory $contentFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\SessionFactory $sessionFactory,
        \Convert\CustomerContent\Model\ContentFactory $contentFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->sessionFactory = $sessionFactory;
        $this->contentFactory = $contentFactory;
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
            'Convert_CustomerContent::menu_top_content'
        )->addBreadcrumb(
            __('Manage Content'),
            __('Manage Content')
        )->addBreadcrumb(
            __('Manage Content'),
            __('Manage Content')
        );
        return $resultPage;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('content_id');
        $model = $this->contentFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This Content no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $data = $this->sessionFactory->create()->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('content', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Content') : __('New Content'),
            $id ? __('Edit Content') : __('New Content')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Content'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getTitle() : __('New Content'));
        return $resultPage;
    }
}
