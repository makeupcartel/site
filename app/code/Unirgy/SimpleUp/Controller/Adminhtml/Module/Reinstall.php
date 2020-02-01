<?php

namespace Unirgy\SimpleUp\Controller\Adminhtml\Module;

use \Magento\Backend\App\Action\Context;

use \Magento\Backend\Model\Auth\Session;

use \Magento\Framework\Controller\Result\RedirectFactory;

use Magento\Framework\View\Result\PageFactory;
use \Unirgy\SimpleUp\Controller\Adminhtml\AbstractModule;

use \Unirgy\SimpleUp\Model\ResourceModel\Setup;


class Reinstall extends AbstractModule
{
    /**
     * @var RedirectFactory
     */
    protected $_controllerResultRedirectFactory;

    public function __construct(Context $context,
                                PageFactory $pageFactory)
    {
        parent::__construct($context, $pageFactory);
    }

    public function execute()
    {
        throw new \Exception(__METHOD__ . " not implemented");

        try {
            /* @var $installer Setup */
            $installer = new Setup('usimpleup_setup');
            $installer->reinstall();
            $this->messageManager->addSuccess(__("Module DB files reinstalled"));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->_controllerResultRedirectFactory->create()->setPath('*/*/');
    }
}
