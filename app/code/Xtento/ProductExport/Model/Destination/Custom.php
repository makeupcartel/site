<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2019-08-27T13:21:15+00:00
 * File:          app/code/Xtento/ProductExport/Model/Destination/Custom.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model\Destination;

class Custom extends AbstractClass
{
    public function testConnection()
    {
        $this->initConnection();
        if (!$this->getDestination()->getBackupDestination()) {
            $this->getDestination()->setLastResult($this->getTestResult()->getSuccess())->setLastResultMessage($this->getTestResult()->getMessage())->save();
        }
        return $this->getTestResult();
    }

    public function initConnection()
    {
        $this->setDestination($this->destinationFactory->create()->load($this->getDestination()->getId()));
        $testResult = new \Magento\Framework\DataObject();
        $this->setTestResult($testResult);
        $customClass = false;
        try {
            $customClass = $this->objectManager->create($this->getDestination()->getCustomClass());
        } catch (\Exception $e) {}
        if (!$customClass) {
            $this->getTestResult()->setSuccess(false)->setMessage(__('Custom class NOT found.'));
            return false;
        } else {
            $this->getTestResult()->setSuccess(true)->setMessage(__('Custom class found and ready to use.'));
            return true;
        }
    }

    public function saveFiles($fileArray)
    {
        if (empty($fileArray)) {
            return [];
        }
        // Init connection
        if (!$this->initConnection()) {
            return [];
        }
        // Call custom class
        $this->objectManager->create($this->getDestination()->getCustomClass())->saveFiles($fileArray);
        return array_keys($fileArray);
    }
}