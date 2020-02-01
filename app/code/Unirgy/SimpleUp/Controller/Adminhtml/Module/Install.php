<?php

namespace Unirgy\SimpleUp\Controller\Adminhtml\Module;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use Unirgy\SimpleUp\Controller\Adminhtml\AbstractModule;
use Unirgy\SimpleUp\Helper\Data as HelperData;


class Install extends AbstractModule
{
    /**
     * @var HelperData
     */
    protected $_simpleUpHelper;

    /**
     * @var ManagerInterface
     */
    protected $_frameworkMessageManagerInterface;

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
        return;
        try {
            $uris = $this->getRequest()->getPost('uri');
            foreach ($uris as $i=>$uri) if (!$uri) unset($uris[$i]);
            if (!$uris) {
                throw new Exception(__('No modules to install'));
            }
            $this->_simpleUpHelper->installModules($uris);
            $this->messageManager->addSuccess(__('New modules has been downoaded and installed'));
        } catch (Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }
}
