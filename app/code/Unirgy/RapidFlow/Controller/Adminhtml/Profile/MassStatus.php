<?php

namespace Unirgy\RapidFlow\Controller\Adminhtml\Profile;


use Unirgy\RapidFlow\Model\Profile;

class MassStatus extends AbstractProfile
{

    public function execute()
    {
        $profileIds = $this->getRequest()->getParam('profiles');
        if (!is_array($profileIds)) {
            $this->messageManager->addErrorMessage(__('Please select profile(s)'));
        } else {
            try {
                foreach ($profileIds as $profileId) {
                    $profile = $this->_profile
                        ->load($profileId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->messageManager->addSuccessMessage(
                    __('Total of %1 record(s) were successfully updated', count($profileIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
}
