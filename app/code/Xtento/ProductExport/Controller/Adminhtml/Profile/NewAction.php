<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2018-08-31T14:17:53+00:00
 * File:          app/code/Xtento/ProductExport/Controller/Adminhtml/Profile/NewAction.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Controller\Adminhtml\Profile;

class NewAction extends \Xtento\ProductExport\Controller\Adminhtml\Profile
{
    /**
     * Forward to edit
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $this->messageManager->addNoticeMessage(__('You will be able to select one of the ready-to-use product feed profiles after clicking "Continue".'));
        $result = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_FORWARD);
        return $result->forward('edit');
    }
}