<?php

namespace Unirgy\SimpleLicense\Controller\Adminhtml\License;

use Unirgy\SimpleLicense\Controller\Adminhtml\AbstractLicense;
use Unirgy\SimpleLicense\Helper\ProtectedCode;


class AddLicense extends AbstractLicense
{
    public function execute()
    {
        try {
            $key = $this->getRequest()->getPost('license_key');
            $install = !!$this->getRequest()->getPost('download_install');
            ProtectedCode::retrieveLicense($key, $install);
            $this->messageManager->addSuccess(__('The license has been added: %1', $key));
            if($install){
                $installNotice = <<<CLI
Now you have to login to your server's command line and perform:</br>
<code>
    bin/magento module:enable <list all module names here>
</code>
CLI;
                $this->messageManager->addNotice($installNotice);
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->_redirect('usimpleup/module');
    }
}
