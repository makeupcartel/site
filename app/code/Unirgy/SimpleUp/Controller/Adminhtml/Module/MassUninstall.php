<?php

namespace Unirgy\SimpleUp\Controller\Adminhtml\Module;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\View\Result\PageFactory;
use Unirgy\SimpleUp\Controller\Adminhtml\AbstractModule;
use Unirgy\SimpleUp\Helper\Data as HelperData;


class MassUninstall extends AbstractModule
{
    /**
     * @var HelperData
     */
    protected $_simpleUpHelper;

    /**
     * @var RedirectFactory
     */
    protected $_controllerResultRedirectFactory;

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
            if (!$modules) {
                throw new \Exception(__('No modules to uninstall'));
            }
            $this->_simpleUpHelper->uninstallModules($modules);
            $this->messageManager->addSuccess(__('Modules have been uninstalled'));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }
}
