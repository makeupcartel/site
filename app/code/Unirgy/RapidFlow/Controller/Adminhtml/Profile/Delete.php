<?php

namespace Unirgy\RapidFlow\Controller\Adminhtml\Profile;


use Unirgy\RapidFlow\Model\Profile;

class Delete extends AbstractProfile
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id > 0) {
            try {
                $model = $this->_profile;

                $model->setId($id)
                    ->delete();

                $this->messageManager->addSuccessMessage(__('Profile was successfully deleted'));
                return $this->_redirect('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $this->_redirect('*/*/edit', ['id' => $id]);
            }
        }
        return $this->_redirect('*/*/');
    }
}
