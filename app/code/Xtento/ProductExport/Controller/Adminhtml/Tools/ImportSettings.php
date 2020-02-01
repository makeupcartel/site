<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2017-04-27T19:29:20+00:00
 * File:          app/code/Xtento/ProductExport/Controller/Adminhtml/Tools/ImportSettings.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Controller\Adminhtml\Tools;

class ImportSettings extends \Xtento\ProductExport\Controller\Adminhtml\Tools
{
    /**
     * @var \Xtento\ProductExport\Helper\Tools
     */
    protected $toolsHelper;

    /**
     * ExportSettings constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Xtento\ProductExport\Helper\Module $moduleHelper
     * @param \Xtento\XtCore\Helper\Cron $cronHelper
     * @param \Xtento\ProductExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Xtento\ProductExport\Model\ProfileFactory $profileFactory
     * @param \Xtento\ProductExport\Model\DestinationFactory $destinationFactory
     * @param \Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface $requestData
     * @param \Xtento\XtCore\Helper\Utils $utilsHelper
     * @param \Xtento\ProductExport\Helper\Tools $toolsHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Xtento\ProductExport\Helper\Module $moduleHelper,
        \Xtento\XtCore\Helper\Cron $cronHelper,
        \Xtento\ProductExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Xtento\ProductExport\Model\ProfileFactory $profileFactory,
        \Xtento\ProductExport\Model\DestinationFactory $destinationFactory,
        \Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface $requestData,
        \Xtento\XtCore\Helper\Utils $utilsHelper,
        \Xtento\ProductExport\Helper\Tools $toolsHelper
    ) {
        parent::__construct($context, $moduleHelper, $cronHelper, $profileCollectionFactory, $scopeConfig, $profileFactory, $destinationFactory, $requestData, $utilsHelper);
        $this->toolsHelper = $toolsHelper;
    }

    /**
     * Import action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\Controller\Result\Raw
     * @throws \Exception
     */
    public function execute()
    {
        // Check for uploaded file
        $settingsFile = $this->_request->getFiles('settings_file');
        if (!isset($settingsFile['tmp_name']) || empty($settingsFile['tmp_name'])) {
            $this->messageManager->addErrorMessage(__('No settings file has been uploaded.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }
        $uploadedFile = $settingsFile['tmp_name'];
        // Check if data should be updated or added
        $updateByName = false;
        if ($this->getRequest()->getPost('update_by_name', false) == 'on') {
            $updateByName = true;
        }
        // Counters
        $addedCounter = ['profiles' => 0, 'destinations' => 0];
        $updatedCounter = ['profiles' => 0, 'destinations' => 0];
        $errorMessage = "";
        // Load JSON settings
        $jsonData = file_get_contents($uploadedFile);
        if (!$this->toolsHelper->importSettingsFromJson($jsonData, $addedCounter, $updatedCounter, $updateByName, $errorMessage)) {
            $this->messageManager->addErrorMessage($errorMessage);
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }
        // Done
        $this->messageManager->addSuccessMessage(__('%1 profiles have been added, %2 profiles have been updated, %3 destinations have been added, %4 destinations have been updated.', $addedCounter['profiles'], $updatedCounter['profiles'], $addedCounter['destinations'], $updatedCounter['destinations']));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/');
        return $resultRedirect;
    }
}