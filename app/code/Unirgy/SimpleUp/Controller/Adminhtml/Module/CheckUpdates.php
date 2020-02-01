<?php

namespace Unirgy\SimpleUp\Controller\Adminhtml\Module;

use \Magento\Backend\App\Action\Context;

use \Magento\Backend\Model\Auth\Session;

use \Magento\Framework\Controller\Result\RedirectFactory;

use \Magento\Framework\Message\ManagerInterface;

use Magento\Framework\View\Result\PageFactory;
use \Unirgy\SimpleUp\Controller\Adminhtml\AbstractModule;

use \Unirgy\SimpleUp\Helper\Data as HelperData;

/**
 * Class CheckUpdates
 * @package Unirgy\SimpleUp\Controller\Adminhtml\Module
 */
class CheckUpdates extends AbstractModule
{
    /**
     * @var HelperData
     */
    protected $_simpleUpHelper;

    public function __construct(Context $context,
        PageFactory $pageFactory,
        HelperData $simpleUpHelper)
    {
        $this->_simpleUpHelper = $simpleUpHelper;

        parent::__construct($context, $pageFactory);
    }

    public function execute()
    {
        try {
            $modules = $this->getRequest()->getPost('modules');
            $this->_simpleUpHelper->checkUpdates();
            $this->messageManager->addSuccess(__('Version updates have been fetched'));
        } catch (\Exception $e) {
echo "<pre>";
print_r($e);
exit;
            $this->messageManager->addError($e->getMessage());
        }
//        $this->resultRedirectFactory->create()->setPath('*/*/');
        $this->_redirect('*/*/');
    }
}
