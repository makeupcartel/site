<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2018-09-26T12:48:45+00:00
 * File:          app/code/Xtento/ProductExport/Controller/Adminhtml/Profile/CreateFeedDestination.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Controller\Adminhtml\Profile;

use Magento\Store\Model\StoreManagerInterface;

class CreateFeedDestination extends \Xtento\ProductExport\Controller\Adminhtml\Profile
{
    /**
     * @var \Xtento\ProductExport\Model\ResourceModel\Destination\CollectionFactory
     */
    protected $destinationCollectionFactory;

    /**
     * @var \Xtento\ProductExport\Model\DestinationFactory
     */
    protected $destinationFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * CreateFeedDestination constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Xtento\ProductExport\Helper\Module $moduleHelper
     * @param \Xtento\XtCore\Helper\Cron $cronHelper
     * @param \Xtento\ProductExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Xtento\ProductExport\Helper\Entity $entityHelper
     * @param \Xtento\ProductExport\Model\ProfileFactory $profileFactory
     * @param \Xtento\ProductExport\Model\ResourceModel\Destination\CollectionFactory $destinationCollectionFactory
     * @param \Xtento\ProductExport\Model\DestinationFactory $destinationFactory
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Xtento\ProductExport\Helper\Module $moduleHelper,
        \Xtento\XtCore\Helper\Cron $cronHelper,
        \Xtento\ProductExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Xtento\ProductExport\Helper\Entity $entityHelper,
        \Xtento\ProductExport\Model\ProfileFactory $profileFactory,
        \Xtento\ProductExport\Model\ResourceModel\Destination\CollectionFactory $destinationCollectionFactory,
        \Xtento\ProductExport\Model\DestinationFactory $destinationFactory,
        StoreManagerInterface $storeManager,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList
    ) {
        $this->destinationCollectionFactory = $destinationCollectionFactory;
        $this->destinationFactory = $destinationFactory;
        $this->storeManager = $storeManager;
        $this->directoryList = $directoryList;
        parent::__construct($context, $moduleHelper, $cronHelper, $profileCollectionFactory, $registry, $escaper, $scopeConfig, $dateFilter, $entityHelper, $profileFactory);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);

        // Configuration
        $destinationName = 'Public Feed Folder';
        if ($this->directoryList->getUrlPath('pub') == '') {
            // Pub folder is being used, use pub/media folder instead (also for M2 Cloud)
            $destinationPath = './pub/media/feeds/';
        } else {
            $destinationPath = './feeds/';
        }
        $testResult = true;

        // Check if destination already exists
        $destinationCollection = $this->destinationCollectionFactory->create();
        $destinationCollection->addFieldToFilter('name', $destinationName);
        if ($destinationCollection->count() > 0) {
            $destinationId = $destinationCollection->getFirstItem()->getId();
        } else {
            // Create destination
            $destination = $this->destinationFactory->create();
            $destination->setType(\Xtento\ProductExport\Model\Destination::TYPE_LOCAL);
            $destination->setName($destinationName);
            $destination->setPath($destinationPath);
            $destination->setLastModification(time());
            $destination->save();

            // Test destination
            $this->registry->register('productexport_destination', $destination, true);
            $testResult = $this->testConnection();

            // Get destination ID
            $destinationId = $destination->getId();
        }

        $feedFilename = $this->getRequest()->getParam('filename');
        $feedUrl = rtrim($this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB), '/') . preg_replace('/^\/pub/', '', ltrim($destinationPath, '.')) . $feedFilename;
        if ($testResult === true) {
            $resultPage->setData(['success' => true, 'destination_id' => $destinationId, 'feed_url' => $feedUrl]);
        } else {
            $resultPage->setData(['success' => false, 'destination_id' => $destinationId, 'warning' => $testResult, 'feed_url' => $feedUrl]);
        }
        return $resultPage;
    }

    protected function testConnection()
    {
        $destination = $this->registry->registry('productexport_destination');
        $testResult = $this->_objectManager->create(
            '\Xtento\ProductExport\Model\Destination\\' . ucfirst($destination->getType())
        )->setDestination($destination)->testConnection();
        if (!$testResult->getSuccess()) {
            return $testResult->getMessage();
        }
        return true;
    }
}
