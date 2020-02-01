<?php

namespace Unirgy\RapidFlow\Controller\Adminhtml\Profile;

use Magento\Framework\Model\Resource;
use Unirgy\RapidFlow\Model\Profile;

class Resume extends AbstractProfile
{
    public function execute()
    {
        try {
            $id = $this->getRequest()->getParam('id', false);
            if (!$id) {
                throw new \Exception("INVALID PROFILE ID");
            }
            $this->_profile->load($id)->factory()->resume()->save();
            $this->messageManager->addSuccessMessage(__('Profile resumed successfully'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $this->_redirect('*/*/edit', ['_current'=>true]);
    }
}
