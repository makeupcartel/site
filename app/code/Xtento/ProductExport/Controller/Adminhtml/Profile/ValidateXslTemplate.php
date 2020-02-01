<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2019-08-27T12:45:18+00:00
 * File:          app/code/Xtento/ProductExport/Controller/Adminhtml/Profile/ValidateXslTemplate.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Controller\Adminhtml\Profile;

use Magento\Framework\App\Filesystem\DirectoryList;

class ValidateXslTemplate extends \Xtento\ProductExport\Controller\Adminhtml\Profile
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $systemTmpDir;

    /**
     * @var \Xtento\ProductExport\Model\ExportFactory
     */
    protected $exportFactory;

    /**
     * ValidateXslTemplate constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Xtento\ProductExport\Helper\Module $moduleHelper
     * @param \Xtento\XtCore\Helper\Cron $cronHelper
     * @param \Xtento\ProductExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Xtento\ProductExport\Helper\Entity $entityHelper
     * @param \Xtento\ProductExport\Model\ProfileFactory $profileFactory
     * @param \Xtento\ProductExport\Model\ExportFactory $exportFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Xtento\ProductExport\Helper\Module $moduleHelper,
        \Xtento\XtCore\Helper\Cron $cronHelper,
        \Xtento\ProductExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Xtento\ProductExport\Helper\Entity $entityHelper,
        \Xtento\ProductExport\Model\ProfileFactory $profileFactory,
        \Xtento\ProductExport\Model\ExportFactory $exportFactory
    ) {
        parent::__construct($context, $moduleHelper, $cronHelper, $profileCollectionFactory, $registry, $escaper, $scopeConfig, $dateFilter, $entityHelper, $profileFactory);
        $this->systemTmpDir = $filesystem->getDirectoryWrite(DirectoryList::TMP);
        $this->exportFactory = $exportFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Raw|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Raw $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);

        $xslTemplate = $this->getRequest()->getPost('xsl_template', false);
        if (!$xslTemplate || empty($xslTemplate)) {
            $resultPage->setContents(__('No XSL Template supplied.'));
            return $resultPage;
        }
        $exportId = $this->getRequest()->getPost('test_id', false);
        $profileId = $this->getRequest()->getPost('profile_id', false);
        $profile = $this->profileFactory->create()->load($profileId);
        if (!$profile->getId()) {
            $this->messageManager->addErrorMessage(__('This profile no longer exists.'));
            /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(
                \Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT
            );
            return $resultRedirect->setPath('*/*/');
        }
        $this->registry->unregister('productexport_profile');
        $this->registry->register('productexport_profile', $profile);

        $profile->setXslTemplate($xslTemplate);
        // Export
        try {
            $output = "";
            $outputFiles = $this->exportFactory->create()->setProfile($profile)->testExport($exportId);
            if (!is_array($outputFiles)) {
                $output = $outputFiles;
            } else {
                $count = 0;
                foreach ($outputFiles as $filename => $outputFile) {
                    $count++;
                    if ($count > 1) {
                        $output .= "\n";
                    }
                    $output .= "File: " . $filename . "\n\n" . $outputFile;
                }
                // Store file so it can be served to the browser
                if ($this->getRequest()->getParam('serve_to_browser', false)) {
                    try {
                        $serializedArray = json_encode($outputFiles);
                    } catch (\Exception $e) {
                        $serializedArray = '';
                    }
                    if (!$this->systemTmpDir->writeFile('profile_' . $profileId, $serializedArray)) {
                        $output .= __(
                            "\n\nAttention: Could not save temporary file to store test export for serving the file to the browser."
                        );
                    }
                }
            }
            $resultPage->setContents($output);
            return $resultPage;
        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();
            if (preg_match('/have been exported/', $e->getMessage())) {
                $errorMsg .= "\n\nIf the ID you tried to export exists in Magento, make sure you set up no filters in the 'Stores / Filters' tab that stop the object from being exported.";
            }
            $resultPage->setContents(__('Error: %1', $errorMsg));
        }

        return $resultPage;
    }
}
