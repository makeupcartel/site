<?php

namespace Unirgy\SimpleLicense\Controller\Adminhtml\License;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Unirgy\SimpleLicense\Controller\Adminhtml\AbstractLicense;
use Unirgy\SimpleLicense\Controller\Adminhtml\License;
use Unirgy\SimpleLicense\Model\LicenseFactory;


class MassRemove extends AbstractLicense
{
    /**
     * @var LicenseFactory
     */
    protected $licenseFacotry;

    /**
     * @var ManagerInterface
     */
    protected $_frameworkMessageManagerInterface;

    /**
     * @var RedirectFactory
     */
    protected $_controllerResultRedirectFactory;

    public function __construct(Context $context,
        LicenseFactory $licenseFactory,
        RedirectFactory $controllerResultRedirectFactory)
    {
        $this->licenseFacotry = $licenseFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $ids = $this->getRequest()->getPost('licenses');
            if (!$ids) {
                throw new \RuntimeException(__('No licenses to remove'));
            }
            $licenses = $this->licenseFacotry->create()->getCollection()->addFieldToFilter('license_id', $ids);
            /** @var \Unirgy\SimpleLicense\Model\License $l */
            foreach ($licenses as $l) {
                $l->delete();
            }
            $this->messageManager->addSuccess(__('Licenses have been removed'));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->_redirect('usimpleup/module');
    }
}
