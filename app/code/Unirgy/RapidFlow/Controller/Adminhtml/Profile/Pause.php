<?php

namespace Unirgy\RapidFlow\Controller\Adminhtml\Profile;


use Unirgy\RapidFlow\Model\Profile;

class Pause extends AbstractProfile
{

    public function execute()
    {
        try {
            $id = $this->getRequest()->getParam('id', false);
            if (!$id) {
                throw new \Exception('INVALID PROFILE ID');
            }
            $this->_profile->load($id)->factory()->pause()->save();
            $this->messageManager->addSuccessMessage(__('Profile paused successfully'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $this->_redirect('*/*/edit', ['_current'=>true]);
    }
}
