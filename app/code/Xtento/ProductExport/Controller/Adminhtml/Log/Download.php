<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2016-04-21T11:05:27+00:00
 * File:          app/code/Xtento/ProductExport/Controller/Adminhtml/Log/Download.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Controller\Adminhtml\Log;

use Magento\Framework\Exception\LocalizedException;

class Download extends \Xtento\ProductExport\Controller\Adminhtml\Log
{
    /**
     * @var \Xtento\XtCore\Helper\Utils
     */
    protected $utilsHelper;

    /**
     * Download constructor.
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

        $logId = intval($this->getRequest()->getParam('id', false));
        if (!$logId) {
            $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $this->messageManager->addWarningMessage(__('No log ID specified.'));
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        $exportedFiles = $this->getFilesForLogId($logId);
        if (!$exportedFiles) {
            $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        /** @var \Magento\Framework\Controller\Result\Raw $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
        $file = $this->utilsHelper->prepareFilesForDownload($exportedFiles);
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
