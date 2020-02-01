<?php

namespace Unirgy\SimpleUp\Controller\Adminhtml\Module;

use \Magento\Backend\App\Action\Context;

use \Magento\Backend\Model\Auth\Session;

use \Magento\Framework\Controller\Result\RedirectFactory;

use \Magento\Framework\Message\ManagerInterface;

use Magento\Framework\View\Result\PageFactory;
use \Unirgy\SimpleUp\Controller\Adminhtml\AbstractModule;

use \Unirgy\SimpleUp\Helper\Data as HelperData;


class MassUpgrade extends AbstractModule
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
            if (!$modules) {
                throw new \Exception(__('No modules to upgrade'));
            }
            $this->_simpleUpHelper->upgradeModules($modules);
            $this->messageManager->addSuccess(__('Modules have been upgraded'));
//            $enable = join(" ", $modules);
            $upgradeNotice = <<<CLI
Now you have to login to your server's command line and perform:</br>
If you just installed Unirgy module for first time:</br>
<code>
    bin/magento module:enable <list all module names here>
</code></br>
Otherwise simply:</br>
<code>
    bin/magento setup:upgrade
</code>
CLI;
            $this->messageManager->addNotice(__($upgradeNotice));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }
}
