<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2017-03-09T13:59:33+00:00
 * File:          app/code/Xtento/ProductExport/Controller/Adminhtml/Manual/ManualPost.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Controller\Adminhtml\Manual;

use Magento\Framework\Exception\LocalizedException;

class ManualPost extends \Xtento\ProductExport\Controller\Adminhtml\Manual
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Xtento\ProductExport\Helper\Entity
     */
    protected $entityHelper;

    /**
     * @var \Xtento\XtCore\Helper\Date
     */
    protected $dateHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    protected $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Session\Config\ConfigInterface
     */
    protected $sessionConfig;

    /**
     * @var \Xtento\XtCore\Helper\Utils
     */
    protected $utilsHelper;

    /**
     * @var \Xtento\ProductExport\Model\ExportFactory
     */
    protected $exportFactory;

    /**
     * ManualPost constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Xtento\ProductExport\Helper\Module $moduleHelper
     * @param \Xtento\XtCore\Helper\Cron $cronHelper
     * @param \Xtento\ProductExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Xtento\ProductExport\Model\ProfileFactory $profileFactory
     * @param \Xtento\ProductExport\Helper\Entity $entityHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Xtento\XtCore\Helper\Date $dateHelper
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\Cookie\PhpCookieManager $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\Session\Config\ConfigInterface $sessionConfig
     * @param \Xtento\XtCore\Helper\Utils $utilsHelper
     * @param \Xtento\ProductExport\Model\ExportFactory $exportFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Xtento\ProductExport\Helper\Module $moduleHelper,
        \Xtento\XtCore\Helper\Cron $cronHelper,
        \Xtento\ProductExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Xtento\ProductExport\Model\ProfileFactory $profileFactory,
        \Xtento\ProductExport\Helper\Entity $entityHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Xtento\XtCore\Helper\Date $dateHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\Cookie\PhpCookieManager $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig,
        \Xtento\XtCore\Helper\Utils $utilsHelper,
        \Xtento\ProductExport\Model\ExportFactory $exportFactory
    ) {
        parent::__construct($context, $moduleHelper, $cronHelper, $profileCollectionFactory, $scopeConfig, $profileFactory);
        $this->storeManager = $storeManager;
        $this->entityHelper = $entityHelper;
        $this->dateHelper = $dateHelper;
        $this->localeDate = $localeDate;
        $this->registry = $registry;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionConfig = $sessionConfig;
        $this->utilsHelper = $utilsHelper;
        $this->exportFactory = $exportFactory;
    }

    /**
     * Export action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\Controller\Result\Raw
     * @throws \Exception
     */
    public function execute()
    {
        $profileId = $this->getRequest()->getPost('profile_id');
        $profile = $this->profileFactory->create()->load($profileId);
        if (!$profile->getId()) {
            $this->messageManager->addErrorMessage(__('No profile selected or this profile does not exist anymore.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }
        // Table prefix
        $tablePrefix = "";
        $entityIdField = "entity_id";
        if ($profile->getEntity() == \Xtento\ProductExport\Model\Export::ENTITY_REVIEW) {
            $tablePrefix = "main_table.";
            $entityIdField = "review_id";
        }
        // Prepare filters
        $filters = [];
        if ($this->getRequest()->getPost('entity_from') !== null) {
            $filters[] = [$tablePrefix . $entityIdField => ['from' => $this->getRequest()->getPost('entity_from')]];
        }
        if ($this->getRequest()->getPost('entity_to') !== null && $this->getRequest()->getPost('entity_to') !== '0') {
            $filters[] = [$tablePrefix . $entityIdField => ['to' => $this->getRequest()->getPost('entity_to')]];
        }
        $dateNormalizer = new \Magento\Framework\Data\Form\Filter\Date(
            $this->localeDate->getDateFormat(\IntlDateFormatter::SHORT), $this->_localeResolver
        );
        $dateRangeFilter = [];
        if ($this->getRequest()->getPost('daterange_from') != '') {
            $dateRangeFilter['datetime'] = true;
            $fromDate = $dateNormalizer->inputFilter($this->getRequest()->getPost('daterange_from'));
            $fromDate = $this->localeDate->scopeDate(null, $fromDate, true);
            $fromDate->setTimezone(new \DateTimeZone('UTC'));
            $dateRangeFilter['from'] = $fromDate->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        }
        if ($this->getRequest()->getPost('daterange_to') != '') {
            $dateRangeFilter['datetime'] = true;
            $toDate = $dateNormalizer->inputFilter($this->getRequest()->getPost('daterange_to'));
            $toDate = $this->localeDate->scopeDate(null, $toDate, true);
            $toDate->add(new \DateInterval('P1D'));
            $toDate->sub(new \DateInterval('PT1S')); // So the "next day, 12:00:00am" is not included
            $toDate->setTimezone(new \DateTimeZone('UTC'));
            $dateRangeFilter['to'] = $toDate->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        }
        $profileFilterCreatedLastXDays = $profile->getData('export_filter_last_x_days');
        if (!empty($profileFilterCreatedLastXDays) || $profileFilterCreatedLastXDays == '0') {
            $profileFilterCreatedLastXDays = preg_replace('/[^0-9]/', '', $profileFilterCreatedLastXDays);
            if ($profileFilterCreatedLastXDays >= 0) {
                $dateToday = $this->localeDate->date();
                $dateToday->sub(new \DateInterval('P' . $profileFilterCreatedLastXDays . 'D'));
                $dateToday->setTime(0, 0, 0);
                $dateToday->setTimezone(new \DateTimeZone('UTC'));
                $dateRangeFilter['datetime'] = true;
                $dateRangeFilter['from'] = $dateToday->format(
                    \Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT
                );
            }
        }
        $profileFilterOlderThanXMinutes = $profile->getData('export_filter_older_x_minutes');
        if (!empty($profileFilterOlderThanXMinutes)) {
            $profileFilterOlderThanXMinutes = intval(preg_replace('/[^0-9]/', '', $profileFilterOlderThanXMinutes));
            if ($profileFilterOlderThanXMinutes > 0) {
                $dateToday = $this->localeDate->date();
                $dateToday->sub(new \DateInterval('PT' . $profileFilterOlderThanXMinutes . 'M'));
                $dateToday->setTimezone(new \DateTimeZone('UTC'));
                $dateRangeFilter['datetime'] = true;
                $dateRangeFilter['to'] = $dateToday->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
            }
        }
        if (!empty($dateRangeFilter)) {
            $filters[] = [$tablePrefix . 'created_at' => $dateRangeFilter];
        }
        $profileFilterUpdatedLastXMinutes = $profile->getData('export_filter_updated_last_x_minutes');
        if (!empty($profileFilterUpdatedLastXMinutes)) {
            $profileFilterUpdatedLastXMinutes = preg_replace('/[^0-9]/', '', $profileFilterUpdatedLastXMinutes);
            if ($profileFilterUpdatedLastXMinutes >= 0) {
                $dateToday = $this->localeDate->date();
                $dateToday->sub(new \DateInterval('PT' . $profileFilterUpdatedLastXMinutes . 'M'));
                $dateToday->setTimezone(new \DateTimeZone('UTC'));
                $updatedAtFilter = [];
                $updatedAtFilter['datetime'] = true;
                $updatedAtFilter['from'] = $dateToday->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
                $filters[] = [$tablePrefix . 'updated_at' => $updatedAtFilter];
            }
        }
        #var_dump($filters); die();
        // Export
        $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setDurationOneYear()
            ->setPath($this->_getSession()->getCookiePath())
            ->setDomain($this->_getSession()->getCookieDomain());
        try {
            $beginTime = time();
            $exportModel = $this->exportFactory->create()->setProfile($profile);
            if ($this->getRequest()->getPost('force_status') != '') {
                $exportModel->setForceChangeStatus($this->getRequest()->getPost('force_status'));
            }
            if ($this->getRequest()->getPost('filter_new_only') == 'on') {
                $exportModel->setExportFilterNewOnly(true);
            }
            $exportedFiles = $exportModel->manualExport($filters);
            $endTime = time();
            $successMessage = __(
                'Export of %1 %2 completed successfully in %3 seconds. ' .
                'Click <a href="%4">here</a> to download exported files.',
                $this->registry->registry('productexport_log')->getRecordsExported(),
                $this->entityHelper->getPluralEntityName($profile->getEntity()),
                ($endTime - $beginTime),
                $this->getUrl(
                    'xtento_productexport/log/download',
                    ['id' => $this->registry->registry('productexport_log')->getId()]
                )
            );
            if ($this->getRequest()->getPost('start_download', false)) {
                $this->cookieManager->setPublicCookie('fileDownload', 'true', $cookieMetadata);
                $this->cookieManager->setPublicCookie('lastMessage', $successMessage, $cookieMetadata);
                if ($this->registry->registry('productexport_log')->getResult(
                    ) !== \Xtento\ProductExport\Model\Log::RESULT_SUCCESSFUL
                ) {
                    $this->cookieManager->setPublicCookie(
                        'lastErrorMessage',
                        __(nl2br($this->registry->registry('productexport_log')->getResultMessage())),
                        $cookieMetadata
                    );
                } else {
                    $this->cookieManager->setPublicCookie('lastErrorMessage', '', $cookieMetadata);
                }
                /** @var \Magento\Framework\Controller\Result\Raw $resultPage */
                $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
                $file = $this->utilsHelper->prepareFilesForDownload($exportedFiles);
                if (empty($file)) {
                    throw new LocalizedException(__('No files have been exported. Please check your XSL Template and/or profile filters.'));
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
            } else {
                $this->messageManager->addComplexSuccessMessage('backendHtmlMessage',
                    [
                        'html' => (string)$successMessage
                    ]
                );
                if ($this->registry->registry('productexport_log')->getResult(
                    ) !== \Xtento\ProductExport\Model\Log::RESULT_SUCCESSFUL
                ) {
                    $this->messageManager->addErrorMessage(
                        __(nl2br($this->registry->registry('productexport_log')->getResultMessage()))
                    );
                }
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultFactory->create(
                    \Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT
                );
                $resultRedirect->setPath('xtento_productexport/manual/index', ['profile_id' => $profile->getId()]);
                return $resultRedirect;
            }
        } catch (\Exception $e) {
            if ($this->getRequest()->getPost('start_download', false)) {
                $this->cookieManager->setPublicCookie('lastErrorMessage', __(nl2br($e->getMessage())), $cookieMetadata);
                $this->cookieManager->setPublicCookie('lastMessage', false, $cookieMetadata);
                $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
                $resultPage->setContents('failed');
                return $resultPage;
            } else {
                $this->messageManager->addWarningMessage(__('%1', nl2br($e->getMessage())));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setPath('xtento_productexport/manual/index', ['profile_id' => $profile->getId()]);
                return $resultRedirect;
            }
        }
    }
}