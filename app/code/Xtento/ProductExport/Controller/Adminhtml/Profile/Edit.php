<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2018-12-13T19:26:53+00:00
 * File:          app/code/Xtento/ProductExport/Controller/Adminhtml/Profile/Edit.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Controller\Adminhtml\Profile;

class Edit extends \Xtento\ProductExport\Controller\Adminhtml\Profile
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $healthCheck = $this->healthCheck();
        if ($healthCheck !== true) {
            $resultRedirect = $this->resultFactory->create(
                \Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT
            );
            return $resultRedirect->setPath($healthCheck);
        }

        $id = $this->getRequest()->getParam('id');
        $model = $this->profileFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This profile no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultFactory->create(
                    \Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT
                );
                return $resultRedirect->setPath('*/*/');
            }
        } else {
            // Default values
            $model->setSaveFilesManualExport(1);
            $model->setSaveFilesLocalCopy(1);
        }

        $session = $this->_session;
        $data = $session->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        } else {
            // Handle certain fields
            $fields = [
                'event_observers',
                'export_filter_product_type',
                'attributes_to_select',
                'export_filter_product_visibility',
                'export_filter_product_status'
            ];
            foreach ($fields as $field) {
                $value = $model->getData($field);
                if (!is_array($value)) {
                    $model->setData($field, explode(',', $value));
                }
            }
        }

        $model->getConditions()->setJsFormObject('rule_conditions_fieldset');
        $this->registry->unregister('productexport_profile');
        $this->registry->register('productexport_profile', $model);

        // Add check for "cronjob export" + "export each order separately" and no unique variable in filename
        if ($model->getExportOneFilePerObject() && $model->getXslTemplate() != '' && !preg_match("/%lastentityid%/", $model->getXslTemplate())
        ) {
            $this->messageManager->addWarningMessage(
                __(
                    'Warning: You have enabled "Export each %1 separately". However, no unique variable was added to the "filename" of your output file in the "Output Format" tab. Because of this, when the export runs, multiple files with the same name will be created, and thus just one file gets saved, which is wrong. Please make sure to add a unique variable to the "filename" in the "Output Format" tab, so multiple files with unique filenames will be generated. A valid unique variable is %lastentityid% for example which is the last %2 id.',
                    $model->getEntity(), $model->getEntity()
                )
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $this->updateMenu($resultPage);

        if ($this->registry->registry('productexport_profile') &&
            $this->registry->registry('productexport_profile')->getId()
        ) {
            $resultPage->getConfig()->getTitle()->prepend(
                __(
                    'Edit %1 Export Profile \'%2\'',
                    $this->entityHelper->getEntityName($this->registry->registry('productexport_profile')->getEntity()),
                    $this->escaper->escapeHtml($this->registry->registry('productexport_profile')->getName())
                )
            );
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('New Profile'));
        }

        if ($session->getProfileDuplicated()) {
            $session->setProfileDuplicated(0);
        }

        return $resultPage;
    }
}