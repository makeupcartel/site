<?php

namespace Unirgy\SimpleLicense\Controller\Adminhtml\License;

use Magento\Backend\App\Action\Context;
use Unirgy\SimpleLicense\Controller\Adminhtml\AbstractLicense;
use Unirgy\SimpleLicense\Helper\ProtectedCode;
use Unirgy\SimpleLicense\Model\LicenseFactory;


class CheckUpdates extends AbstractLicense
{
    /**
     * @var LicenseFactory
     */
    protected $licenseFactory;

    public function __construct(Context $context,
                                LicenseFactory $licenseFactory)
    {
        $this->licenseFactory = $licenseFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $licenses = $this->licenseFactory->create()->getCollection();
            foreach ($licenses as $license) {
                ProtectedCode::retrieveLicense($license);
                try {
                    ProtectedCode::validateLicense($license);
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                }
            }
            $this->messageManager->addSuccess(__('License updates have been fetched'));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->_redirect('usimpleup/module');
    }
}
