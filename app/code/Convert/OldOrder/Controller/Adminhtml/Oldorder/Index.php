<?php
namespace Convert\OldOrder\Controller\Adminhtml\Oldorder;

class Index extends \Magento\Backend\App\Action
{

    const ADMIN_RESOURCE = 'Convert_OldOrder::Orders';

    protected $resultPageFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Convert_OldOrder::Orders')
            ->addBreadcrumb(
                __('Old Orders'), __('Old Orders')
            )->getConfig()->getTitle()->prepend(__('Old Orders'));
        
        return $resultPage;
    }
    
}