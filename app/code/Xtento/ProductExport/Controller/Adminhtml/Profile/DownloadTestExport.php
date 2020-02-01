<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2019-08-27T12:43:40+00:00
 * File:          app/code/Xtento/ProductExport/Controller/Adminhtml/Profile/DownloadTestExport.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Controller\Adminhtml\Profile;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;

class DownloadTestExport extends \Xtento\ProductExport\Controller\Adminhtml\Profile
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $systemTmpDir;

    /**
     * @var \Xtento\XtCore\Helper\Utils
     */
    protected $utilsHelper;

    /**
     * DownloadTestExport constructor.
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
     * @param \Xtento\XtCore\Helper\Utils $utilsHelper
     * @param \Xtento\ProductExport\Model\ProfileFactory $profileFactory
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
        \Xtento\XtCore\Helper\Utils $utilsHelper,
        \Xtento\ProductExport\Model\ProfileFactory $profileFactory
    ) {
        parent::__construct($context, $moduleHelper, $cronHelper, $profileCollectionFactory, $registry, $escaper, $scopeConfig, $dateFilter, $entityHelper, $profileFactory);
        $this->systemTmpDir = $filesystem->getDirectoryWrite(DirectoryList::TMP);
        $this->utilsHelper = $utilsHelper;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     * @throws LocalizedException
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Raw $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);

        $profileId = intval($this->getRequest()->getParam('profile_id', false));
        if (!$profileId) {
            $resultPage->setContents(__('No profile ID supplied.'));
            return $resultPage;
        }

        try {
            $data = json_decode($this->systemTmpDir->readFile('profile_' . $profileId));
        } catch (\Exception $e) {
            $data = [];
        }
        $file = $this->utilsHelper->prepareFilesForDownload($data);
        if (empty($file)) {
            throw new LocalizedException(
                __(
                    'No files have been exported by the XSL Template, or the system temporary folder is not writable. Please check your XSL Template for filters/errors.'
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
