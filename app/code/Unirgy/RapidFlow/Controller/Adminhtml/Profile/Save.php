<?php

namespace Unirgy\RapidFlow\Controller\Adminhtml\Profile;

use Magento\Backend\Model\Session as ModelSession;
use Unirgy\RapidFlow\Helper\Data;
use Unirgy\RapidFlow\Model\Profile;

class Save extends AbstractProfile
{
    public function execute()
    {
        if ($data = $this->getRequest()->getPost()->toArray()) {
            try {
                $model = $this->_profile;

                if ($id = $this->getRequest()->getParam('id')) {
                    $model->load($id);
                }
                if (!isset($data['columns_post'])) {
                    $data['columns_post'] = [];
                }
                if (isset($data['conditions'])) {
                    $data['conditions_post'] = $data['conditions'];
                    unset($data['conditions']);
                }
                if (isset($data['options']['reindex'])) {
                    $data['options']['reindex'] = array_flip($data['options']['reindex']);
                }
                if (isset($data['options']['refresh'])) {
                    $data['options']['refresh'] = array_flip($data['options']['refresh']);
                }
                $model->addData($data);
//                $model = $model->factory();

                if ($model->getCreatedTime() === NULL || $model->getUpdateTime() === NULL) {
                    $model->setCreatedTime(Data::now())
                        ->setUpdateTime(Data::now());
                } else {
                    $model->setUpdateTime(Data::now());
                }

                $model->save();
                $this->messageManager->addSuccessMessage(__('Profile was successfully saved'));
                $this->_session->setFormData(false);

                if ($invokeStatus = $this->getRequest()->getParam('start')) {
                    $model->pending($invokeStatus)->save();
                    $this->messageManager->addSuccessMessage(__('Profile started successfully'));
                }

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $model->getId()]);
                    return;
                }

                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_session->setFormData($data);
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->messageManager->addErrorMessage(__('Unable to find profile to save'));
        $this->_redirect('*/*/');
    }
}
