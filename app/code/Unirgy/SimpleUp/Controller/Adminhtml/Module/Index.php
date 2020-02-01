<?php

namespace Unirgy\SimpleUp\Controller\Adminhtml\Module;

use \Magento\Backend\App\Action\Context;

use \Magento\Backend\Model\Auth\Session;

use \Magento\Framework\App\Config\ScopeConfigInterface;

use \Magento\Framework\Message\ManagerInterface;

use \Magento\Framework\View\LayoutFactory;

use Magento\Framework\View\Result\PageFactory;
use \Unirgy\SimpleUp\Controller\Adminhtml\AbstractModule;


class Index extends AbstractModule
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_config;

    public function __construct(Context $context,
                                ScopeConfigInterface $scopeConfigInterface,
                                PageFactory $resultPageFactory)
    {
        $this->_config = $scopeConfigInterface;

        parent::__construct($context, $resultPageFactory);
    }

    public function execute()
    {
        $page = $this->resultPageFactory->create();

        if ($this->_config->getValue('usimpleup/general/check_ioncube') && !extension_loaded('ionCube Loader') && !function_exists('sg_get_const')) {
            $this->messageManager->addNotice(__('ionCube or SourceGuardian loader is not installed, commercial extensions might not work.'));
        }
        if (!extension_loaded('zip')) {
            $this->messageManager->addError(__('Zip PHP extension is not installed, will not be able to unpack downloaded extensions'));
        }
        if ($this->_config->getValue('usimpleup/ftp/active') && !extension_loaded('ftp')) {
            $this->messageManager->addError(__('FTP PHP extension is not installed, will not be able to install extensions using FTP'));
        }

        $this->_addBreadcrumb(__('Simple Upgrades'), __('Simple Upgrades'));

        $page->setActiveMenu('Unirgy_SimpleUp::usimpleup');
        $page->getConfig()->getTitle()->prepend(__('Unirgy Installer'));
        $page->getConfig()->setPageLayout('admin-2columns-left');
        /** @var  $template \Magento\Backend\Block\Template */
//        $page->getLayout()->createBlock('\Magento\Backend\Block\Template');
        $template = $page->getLayout()->addBlock('\Magento\Backend\Block\Template', 'usimpleup.module.template', 'page.main.container');
        $template->setTemplate('Unirgy_SimpleUp::usimpleup/container.phtml')->setData('module_name', 'Unirgy_SimpleUp');

        return $page;
    }
}
