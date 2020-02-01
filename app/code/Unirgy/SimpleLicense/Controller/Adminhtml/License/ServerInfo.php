<?php

namespace Unirgy\SimpleLicense\Controller\Adminhtml\License;

use Unirgy\SimpleLicense\Controller\Adminhtml\AbstractLicense;
use Unirgy\SimpleLicense\Controller\Adminhtml\License;
use Unirgy\SimpleLicense\Helper\ProtectedCode;


class ServerInfo extends AbstractLicense
{
    public function execute()
    {
        try {
            ProtectedCode::sendServerInfo();
            $this->messageManager->addSuccess(__('Server Info has been sent'));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->_redirect('usimpleup/module');
    }
}
