<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2016-05-30T12:34:08+00:00
 * File:          app/code/Xtento/ProductExport/Controller/Adminhtml/Profile/Duplicate.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Controller\Adminhtml\Profile;

class Duplicate extends \Xtento\ProductExport\Controller\Adminhtml\Profile
{
    /**
     * Duplicate action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);

        $id = (int)$this->getRequest()->getParam('id');
        $model = $this->profileFactory->create();
        $model->load($id);

        if ($id && !$model->getId()) {
            $this->messageManager->addErrorMessage(__('This profile does not exist anymore.'));
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        try {

            $profile = clone $model;
            $profile->setEnabled(0);
            $profile->setId(null);
            $profile->setLastModification(time());
            $profile->setLastExecution(null);
            $profile->save();

            $this->_session->setProfileDuplicated(1);
            $this->messageManager->addSuccessMessage(__('The profile has been duplicated. Please make sure to enable it.'));
            $resultRedirect->setPath('*/*/edit', ['id' => $profile->getId()]);
            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $resultRedirect->setPath('*/*/');
        return $resultRedirect;
    }
}