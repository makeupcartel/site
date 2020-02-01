<?php
namespace Convert\OldOrder\Controller\Adminhtml\OldOrder;

use Convert\OldOrder\Model\OldOrder as Order;


class Delete extends \Magento\Backend\App\Action
{

    protected $resultPageFactory;
    protected $_OldOrderFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Convert\OldOrder\Model\OldOrderFactory $OldOrderFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->_OldOrderFactory = $OldOrderFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        $order = $this->_OldOrderFactory->create()->load($id);

        if(!$order)
        {
            $this->messageManager->addError(__('Unable to process. please, try again.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/', array('_current' => true));
        }

        try{
            $order->delete();
            $this->messageManager->addSuccess(__('Your order has been deleted !'));
        }
        catch(\Exception $e)
        {
            $this->messageManager->addError(__('Error while trying to delete order'));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/index', array('_current' => true));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/index', array('_current' => true));
    }
}