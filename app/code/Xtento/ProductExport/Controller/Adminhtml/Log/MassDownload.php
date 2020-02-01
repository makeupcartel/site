<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2017-12-29T16:08:15+00:00
 * File:          app/code/Xtento/ProductExport/Controller/Adminhtml/Log/MassDownload.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Controller\Adminhtml\Log;

use Magento\Framework\Exception\LocalizedException;

class MassDownload extends \Xtento\ProductExport\Controller\Adminhtml\Log
{
    /**
     * @var \Xtento\XtCore\Helper\Utils
     */
    protected $utilsHelper;

    /**
     * MassDownload constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Xtento\ProductExport\Helper\Module $moduleHelper
     * @param \Xtento\XtCore\Helper\Cron $cronHelper
     * @param \Xtento\ProductExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Xtento\ProductExport\Model\LogFactory $logFactory
     * @param \Xtento\XtCore\Helper\Utils $utilsHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Xtento\ProductExport\Helper\Module $moduleHelper,
        \Xtento\XtCore\Helper\Cron $cronHelper,
        \Xtento\ProductExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Xtento\ProductExport\Model\LogFactory $logFactory,
        \Xtento\XtCore\Helper\Utils $utilsHelper
    ) {
        parent::__construct(
            $context,
            $moduleHelper,
            $cronHelper,
            $profileCollectionFactory,
            $registry,
            $escaper,
            $scopeConfig,
            $logFactory
        );
        $this->utilsHelper = $utilsHelper;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Raw|\Magento\Framework\Controller\ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {

        $logIds = $this->getRequest()->getParam('log', false);
        if (!is_array($logIds)) {
            $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $this->messageManager->addWarningMessage(__('Please select log entries to download.'));
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        $allExportedFiles = [];
        try {
            foreach ($logIds as $logId) {
                $exportedFiles = $this->getFilesForLogId($logId, true);
                if (empty($exportedFiles)) {
                    continue;
                }
                foreach ($exportedFiles as $filename => $content) {
                    if (isset($allExportedFiles[$filename])) {
                        $filename = 'duplicate_filename_' . $logId . '_' . $filename;
                    }
                    $allExportedFiles[$filename] = $content;
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        /** @var \Magento\Framework\Controller\Result\Raw $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
        $file = $this->utilsHelper->prepareFilesForDownload($allExportedFiles);
        if (empty($file)) {
            throw new LocalizedException(
                __(
                    'No files have been exported or the backup files in the export_bkp folder have been deleted from the filesystem. Exported files don\'t exist anymore.'
                )
            );
        }
        $resultPage->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Content-type', 'application/octet-stream', true)
            ->setHeader('Content-Length', strlen($file['data']))
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-Disposition', 'attachment; filename="' . $file['filename'] . '"')
            ->setHeader('Last-Modified', date('r'));
        $resultPage->setContents($file['data']);
        return $resultPage;
    }
}
