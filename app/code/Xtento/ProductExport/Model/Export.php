<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2019-08-27T12:50:15+00:00
 * File:          app/code/Xtento/ProductExport/Model/Export.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model;

use Magento\Framework\Exception\LocalizedException;

class Export extends \Magento\Framework\Model\AbstractModel
{
    // Export entities
    const ENTITY_CATEGORY = 'category';
    const ENTITY_PRODUCT = 'product';
    const ENTITY_REVIEW = 'review';

    // Export types
    const EXPORT_TYPE_TEST = 0; // Test Export
    const EXPORT_TYPE_GRID = 1; // Grid Export
    const EXPORT_TYPE_MANUAL = 2; // From "Manual Export" screen
    const EXPORT_TYPE_CRONJOB = 3; // Cronjob Export
    const EXPORT_TYPE_EVENT = 4; // Export after event

    /**
     * @var \Xtento\XtCore\Helper\Server
     */
    protected $serverHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Xtento\ProductExport\Helper\Module
     */
    protected $moduleHelper;

    /**
     * @var \Xtento\ProductExport\Helper\Entity
     */
    protected $entityHelper;

    /**
     * @var ProfileFactory
     */
    protected $profileFactory;

    /**
     * @var ExportFactory
     */
    protected $exportFactory;

    /**
     * @var LogFactory
     */
    protected $logFactory;

    /**
     * @var HistoryFactory
     */
    protected $historyFactory;

    /**
     * @var \Xtento\ProductExport\Logger\Logger
     */
    protected $xtentoLogger;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Export constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Xtento\XtCore\Helper\Server $serverHelper
     * @param \Xtento\ProductExport\Helper\Module $moduleHelper
     * @param \Xtento\ProductExport\Helper\Entity $entityHelper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param ProfileFactory $profileFactory
     * @param ExportFactory $exportFactory
     * @param LogFactory $logFactory
     * @param HistoryFactory $historyFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Xtento\ProductExport\Logger\Logger $xtentoLogger
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request,
        \Xtento\XtCore\Helper\Server $serverHelper,
        \Xtento\ProductExport\Helper\Module $moduleHelper,
        \Xtento\ProductExport\Helper\Entity $entityHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        ProfileFactory $profileFactory,
        ExportFactory $exportFactory,
        LogFactory $logFactory,
        HistoryFactory $historyFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Xtento\ProductExport\Logger\Logger $xtentoLogger,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->request = $request;
        $this->serverHelper = $serverHelper;
        $this->moduleHelper = $moduleHelper;
        $this->entityHelper = $entityHelper;
        $this->objectManager = $objectManager;
        $this->profileFactory = $profileFactory;
        $this->exportFactory = $exportFactory;
        $this->logFactory = $logFactory;
        $this->historyFactory = $historyFactory;
        $this->scopeConfig = $scopeConfig;
        $this->xtentoLogger = $xtentoLogger;
    }

    /**
     * Get export entities
     *
     * @return array
     */
    public function getEntities()
    {
        $values = [];
        $values[self::ENTITY_CATEGORY] = __('Categories');
        $values[self::ENTITY_PRODUCT] = __('Products');
        $values[self::ENTITY_REVIEW] = __('Product Reviews');
        return $values;
    }

    /**
     * Get export types
     *
     * @return array
     */
    public function getExportTypes()
    {
        $values = [];
        $values[self::EXPORT_TYPE_TEST] = __('Test Export');
        $values[self::EXPORT_TYPE_MANUAL] = __('Manual Export');
        $values[self::EXPORT_TYPE_GRID] = __('Grid Export');
        $values[self::EXPORT_TYPE_CRONJOB] = __('Cronjob Export');
        $values[self::EXPORT_TYPE_EVENT] = __('Event Export');
        return $values;
    }


    /**
     * Validate test XSL Template function
     *
     * @param bool $exportId
     * @return array|\Magento\Framework\Phrase
     * @throws LocalizedException
     */
    public function testExport($exportId = false)
    {
        if (empty($exportId)) {
            return __('No test ID to export specified.');
        }
        $this->setExportType(self::EXPORT_TYPE_TEST);
        $this->_registry->register('is_test_productexport', true, true);
        $filterField = $this->getProfile()->getEntity() == self::ENTITY_REVIEW ? 'main_table.review_id': 'entity_id';
        $filters[] = [$filterField => ['in' => explode(",", $exportId)]];
        $exportedFiles = $this->runExport($filters);
        return $exportedFiles;
    }

    /**
     * Export from a grid
     *
     * @param $exportIds
     * @return array
     * @throws LocalizedException
     */
    public function gridExport($exportIds)
    {
        if (empty($exportIds)) {
            throw new LocalizedException(
                __('No %1 to export specified.', $this->entityHelper->getPluralEntityName($this->getProfile()->getEntity()))
            );
        }
        $this->checkStatus();
        $this->setExportType(self::EXPORT_TYPE_GRID);
        $this->beforeExport();
        $filters[] = ['entity_id' => ['in' => $exportIds]];
        $generatedFiles = $this->runExport($filters);
        if ($this->getProfile()->getSaveFilesManualExport()) {
            $this->saveFiles();
        }
        $this->afterExport();
        return $generatedFiles;
    }

    /**
     * Manual export from "Manual Export" screen
     *
     * @param $filters
     * @return array
     * @throws LocalizedException
     */
    public function manualExport($filters)
    {
        $this->checkStatus();
        $this->setExportType(self::EXPORT_TYPE_MANUAL);
        $this->beforeExport();
        $generatedFiles = $this->runExport($filters);
        if ($this->getProfile()->getSaveFilesManualExport()) {
            $this->saveFiles();
        }
        $this->afterExport();
        return $generatedFiles;
    }

    /**
     * Event based export
     *
     * @param $filters
     * @param bool $forcedCollectionItem
     * @return bool
     * @throws LocalizedException
     */
    public function eventExport($filters, $forcedCollectionItem = false)
    {
        $this->setExportType(self::EXPORT_TYPE_EVENT);
        $this->beforeExport();
        $generatedFiles = $this->runExport($filters, $forcedCollectionItem);
        if (empty($generatedFiles) && ($this->getLogEntry()->getResult() === NULL || $this->getLogEntry()->getResult() === Log::RESULT_SUCCESSFUL)) {
            $this->getLogEntry()->delete();
            return false;
        }
        $this->saveFiles();
        $this->afterExport();
        return true;
    }


    /**
     * Cronjob export
     *
     * @param $filters
     * @return bool
     * @throws LocalizedException
     */
    public function cronExport($filters)
    {
        if (!$this->moduleHelper->isModuleEnabled()) {
            return true;
        }

        $this->setExportType(self::EXPORT_TYPE_CRONJOB);
        $this->beforeExport();
        $generatedFiles = $this->runExport($filters);
        if (empty($generatedFiles) && ($this->getLogEntry()->getResult() === NULL || $this->getLogEntry()->getResult() === Log::RESULT_SUCCESSFUL)) {
            $this->getLogEntry()->delete();
            return false;
        }
        $this->saveFiles();
        $this->afterExport();
        return true;
    }

    /**
     * Merged export - a special export type where multiple profiles are exported at the same time
     *
     * @param $filters
     * @return array
     * @throws LocalizedException
     */
    public function mergedExport($filters)
    {
        $this->setExportType(self::EXPORT_TYPE_CRONJOB);
        $this->beforeExport();
        $generatedFiles = $this->runExport($filters);
        $this->getLogEntry()->addResultMessage(
            __('Exported in merged export mode.')
        );
        $this->saveFiles();
        $this->afterExport();
        return $generatedFiles;
    }

    /**
     * Called by all export routines, initiates the export
     *
     * @param $filters
     * @param bool $forcedCollectionItem
     * @return array
     * @throws LocalizedException
     */
    protected function runExport($filters, $forcedCollectionItem = false)
    {
        try {
            if (function_exists('set_time_limit')) {
                try {
                    set_time_limit(0);
                } catch (\Exception $e) {}
            }
            $this->serverHelper->increaseMemoryLimit('2048M');
            if (!$this->getProfile()) {
                throw new LocalizedException(__('No profile to export specified.'));
            }
            if (preg_match('/\|merge\:/', $this->getProfile()->getXslTemplate())) {
                // Merge multiple profiles. Format: filename|merge:1,3,4,5 (<- profile ids)
                $generatedFiles = [];
                $mergeConfig = $this->getProfile()->getXslTemplate();
                $mergeResultFilename = array_shift(explode("|", $mergeConfig));
                $mergeConfig = str_replace($mergeResultFilename."|merge:", '', $mergeConfig);
                $profileIds = explode(",", $mergeConfig);
                $recordsExported = 0;
                $generatedFile = "";
                foreach ($profileIds as $profileId) {
                    $profile = $this->profileFactory->create()->load($profileId);
                    if ($profile->getId()) {
                        $exportModel = $this->exportFactory->create()->setProfile($profile);
                        $exportedFiles = $exportModel->mergedExport($filters);
                        foreach ($exportedFiles as $exportedFilename => $exportedData) {
                            $generatedFile .= $exportedData;
                            $recordsExported += $this->_registry->registry('productexport_log')->getRecordsExported();
                        }
                    }
                }
                if ($this->_registry->registry('xtento_productexport_export_variables') !== null) {
                    $replaceableVariables = $this->_registry->registry('xtento_productexport_export_variables');
                    $generatedFilename = preg_replace(
                        array_keys($replaceableVariables),
                        array_values($replaceableVariables),
                        $mergeResultFilename
                    );
                    $generatedFiles[$generatedFilename] = $generatedFile;
                } else {
                    $generatedFiles[$mergeResultFilename] = $generatedFile;
                }
                // Re-register profile, log
                $this->_registry->unregister('productexport_log');
                $this->_registry->unregister('productexport_profile');
                $this->_registry->register('productexport_log', $this->getLogEntry());
                $this->_registry->register('productexport_profile', $this->getProfile());
                $this->getLogEntry()->setRecordsExported($recordsExported);
            } else {
                // Normal export, no merged export
                $returnArray = $this->exportObjects($filters, $forcedCollectionItem);
                if (empty($returnArray) && !$this->getProfile()->getExportEmptyFiles()) {
                    throw new LocalizedException(
                        __('0 %1 have been exported.', $this->entityHelper->getPluralEntityName($this->getProfile()->getEntity()))
                    );
                }
                $this->setReturnArrayWithObjects($returnArray);
                // Get output type
                if ($this->getProfile()->getOutputType() == 'csv') {
                    $type = 'csv';
                } else {
                    if ($this->getProfile()->getOutputType() == 'xml') {
                        $type = 'xml';
                    } else {
                        $type = 'xsl';
                    }
                }
                // Convert data
                if ($this->getProfile()->getExportOneFilePerObject()) {
                    // Create one file per exported object
                    $generatedFiles = [];
                    foreach ($this->getReturnArrayWithObjects() as $returnObject) {
                        $generatedFiles += $this->objectManager->create(
                                '\Xtento\ProductExport\Model\Output\\' . ucfirst($type)
                            )->setProfile($this->getProfile())->convertData([$returnObject]);
                    }
                } else {
                    // Create just one file for all exported objects
                    $generatedFiles = $this->objectManager->create(
                        '\Xtento\ProductExport\Model\Output\\' . ucfirst($type)
                    )->setProfile($this->getProfile())->convertData($this->getReturnArrayWithObjects());
                }
            }
            // Check for empty files
            if (!$this->getProfile()->getExportEmptyFiles()) {
                foreach ($generatedFiles as $filename => $data) {
                    if (strlen($data) === 0) {
                        unset($generatedFiles[$filename]);
                    }
                }
            }
            // Set generated files
            $this->setGeneratedFiles($generatedFiles);
            if (is_array($this->getReturnArrayWithObjects()) && $this->getLogEntry()) {
                $this->getLogEntry()->setRecordsExported(count($this->getReturnArrayWithObjects()));
            }
            return $generatedFiles;
        } catch (\Exception $e) {
            if ($this->getLogEntry()) {
                $result = Log::RESULT_FAILED;
                if (preg_match('/have been exported/', $e->getMessage())) {
                    if ($this->getExportType() == self::EXPORT_TYPE_MANUAL || $this->getExportType(
                        ) == self::EXPORT_TYPE_GRID
                    ) {
                        $result = Log::RESULT_WARNING;
                    } else {
                        return [];
                    }
                }
                $this->getLogEntry()->setResult($result);
                $this->getLogEntry()->addResultMessage($e->getMessage());
                $this->afterExport();
            }
            if ($this->getExportType() == self::EXPORT_TYPE_MANUAL || $this->getExportType(
                ) == self::EXPORT_TYPE_GRID || $this->getExportType() == self::EXPORT_TYPE_TEST
            ) {
                throw new LocalizedException(__($e->getMessage()));
            }
            return [];
        }
    }

    /**
     * Export objects
     *
     * @param $filters
     * @param bool $forcedCollectionItem
     * @return mixed
     */
    protected function exportObjects($filters, $forcedCollectionItem = false)
    {
        $export = $this->objectManager->create(
            '\Xtento\ProductExport\Model\Export\Entity\\' . ucfirst($this->getProfile()->getEntity())
        );
        $export->setExportType($this->getExportType());
        $collection = $export->setCollectionFilters($filters);
        if ($this->getProfile()->getExportFilterNewOnly() &&
            ($this->getExportType() == self::EXPORT_TYPE_CRONJOB || $this->getExportType() == self::EXPORT_TYPE_EVENT)
        ) {
            $this->addExportOnlyNewFilter($collection);
            $export->addExportOnlyNewFilter();
        }
        if ($this->getExportFilterNewOnly() && ($this->getExportType() == self::EXPORT_TYPE_MANUAL
                /* || $this->getExportType() == self::EXPORT_TYPE_GRID*/)
        ) {
            $this->addExportOnlyNewFilter($collection);
        }
        #var_dump($filters);
        #echo $collection->getSelect();
        #echo $collection->count(); die();
        $export->setProfile($this->getProfile());
        return $export->runExport($forcedCollectionItem);
    }

    protected function addExportOnlyNewFilter($collection)
    {
        $joinTable = 'e';
        $checkField = 'entity_id';
        if ($this->getProfile()->getEntity() == self::ENTITY_REVIEW) {
            $joinTable = 'main_table';
            $checkField = 'review_id';
        }
        // Filter and hide objects that have been exported previously
        $collection->getSelect()->joinLeft(
            ['export_history' => $collection->getTable('xtento_productexport_profile_history')],
            $joinTable . '.' . $checkField . ' = export_history.entity_id and ' . $collection->getConnection(
            )->quoteInto(
                'export_history.entity = ?',
                $this->getProfile()->getEntity()
            ) . ' and ' . $collection->getConnection()->quoteInto(
                'export_history.profile_id = ?',
                $this->getProfile()->getId()
            ),
            []
        );
        $collection->getSelect()->where('export_history.entity_id IS NULL');
        #echo $collection->getSelect(); die();
    }

    /*
     * Save files on their destinations
     */
    protected function saveFiles()
    {
        try {
            foreach ($this->getProfile()->getDestinations() as $destination) {
                try {
                    $savedFiles = $destination->saveFiles($this->getGeneratedFiles());
                    if (is_array($this->getFiles()) && is_array($savedFiles)) {
                        $this->setFiles(array_merge($this->getFiles(), $savedFiles));
                    } else {
                        $this->setFiles($savedFiles);
                    }
                } catch (\Exception $e) {
                    $this->getLogEntry()->setResult(Log::RESULT_WARNING);
                    $this->getLogEntry()->addResultMessage($e->getMessage());
                }
            }
        } catch (\Exception $e) {
            $this->getLogEntry()->setResult(Log::RESULT_FAILED);
            $this->getLogEntry()->addResultMessage($e->getMessage());
            if ($this->getExportType() == self::EXPORT_TYPE_MANUAL) {
                throw new LocalizedException(__($e->getMessage()));
            }
        }
    }

    /**
     * Called before every export
     */
    protected function beforeExport()
    {
        $this->setBeginTime(time());
        #$memBefore = memory_get_usage();
        #$timeBefore = time();
        #echo "Before export: " . $memBefore . " bytes / Time: " . $timeBefore . "<br>";
        $logEntry = $this->logFactory->create();
        $logEntry->setCreatedAt(time());
        $logEntry->setProfileId($this->getProfile()->getId());
        $logEntry->setDestinationIds($this->getProfile()->getDestinationIds());
        $logEntry->setExportType($this->getExportType());
        $logEntry->setRecordsExported(0);
        $logEntry->setResultMessage(__('Export started...'));
        $logEntry->save();
        $this->setLogEntry($logEntry);
        $this->_registry->unregister('productexport_log');
        $this->_registry->unregister('productexport_profile');
        $this->_registry->register('productexport_log', $logEntry);
        $this->_registry->register('productexport_profile', $this->getProfile());
        \Xtento\ProductExport\Helper\GracefulDie::enable();
    }

    /**
     * Called after every export
     */
    protected function afterExport()
    {
        if ($this->getLogEntry()->getResult() !== Log::RESULT_FAILED) {
            if ($this->getProfile()->getExportFilterNewOnly() || $this->getExportFilterNewOnly()) {
                $this->createExportHistoryEntries();
            }
        }
        \Xtento\ProductExport\Helper\GracefulDie::disable();
        $this->saveLog();
        $this->_registry->unregister('productexport_profile');
        #echo "After export: " . memory_get_usage() . " (Difference: " . round((memory_get_usage() - $memBefore) / 1024 / 1024, 2) . " MB, " . (time() - $timeBefore) . " Secs) - Count: " . (count($exportIds)) . " -  Per entry: " . round(((memory_get_usage() - $memBefore) / 1024 / 1024) / (count($exportIds)), 2) . "<br>";
        // Dispatch event after export
        $this->_eventManager->dispatch('xtento_productexport_export_after',
            [
                'profile' => $this->getProfile(),
                'log' => $this->getLogEntry(),
                'objects' => $this->getReturnArrayWithObjects(),
                'files' => $this->getGeneratedFiles(),
            ]
        );
        return $this;
    }

    /**
     * Create export history entries after exporting, if enabled for profile. Important for "Export only new ..." feature
     */
    protected function createExportHistoryEntries()
    {
        if ($this->getReturnArrayWithObjects()) {
            // Save exported object ids in the export history
            foreach ($this->getReturnArrayWithObjects() as $object) {
                $historyEntry = $this->historyFactory->create();
                $historyEntry->setProfileId($this->getProfile()->getId());
                $historyEntry->setLogId($this->getLogEntry()->getId());
                $historyEntry->setEntity($this->getProfile()->getEntity());
                $historyEntry->setEntityId($object['entity_id']);
                $historyEntry->setExportedAt(time());
                $historyEntry->save();
            }
        }
    }

    /**
     * Save export log
     */
    protected function saveLog()
    {
        $this->getProfile()->saveLastExecutionNow();
        if (is_array($this->getFiles())) {
            $this->getLogEntry()->setFiles(implode("|", $this->getFiles()));
        }
        $this->getLogEntry()->setResult(
            $this->getLogEntry()->getResult() ? $this->getLogEntry()->getResult() : Log::RESULT_SUCCESSFUL
        );
        $this->getLogEntry()->setResultMessage(
            $this->getLogEntry()->getResultMessages() ? $this->getLogEntry()->getResultMessages() : __(
                'Export of %1 %2 finished in %3 seconds.',
                $this->getLogEntry()->getRecordsExported(),
                $this->getLogEntry()->getRecordsExported() > 0 ? $this->entityHelper->getPluralEntityName(
                    $this->getProfile()->getEntity()
                ) : $this->getProfile()->getEntity(),
                (time() - $this->getBeginTime())
            )
        );
        if ($this->getLogEntry()->getResult() == Log::RESULT_SUCCESSFUL && $this->getLogEntry()->getRecordsExported() == 0) {
            $this->getLogEntry()->delete();
        } else {
            $this->getLogEntry()->save();
        }
        $this->errorEmailNotification();
        #$this->_registry->unregister('productexport_log');
    }

    /**
     * On exception, send error email to debug email set in configuration
     *
     * @return $this
     */
    protected function errorEmailNotification()
    {
        if (!$this->moduleHelper->isDebugEnabled() || $this->moduleHelper->getDebugEmail() == '') {
            return $this;
        }
        if ($this->getLogEntry()->getResult() >= Log::RESULT_WARNING) {
            try {
                /** @var \Magento\Framework\Mail\Message $message */
                $message = $this->objectManager->create('Magento\Framework\Mail\MessageInterface');
                $message->setFrom($this->scopeConfig->getValue('trans_email/ident_general/email'), $this->scopeConfig->getValue('trans_email/ident_general/name'));
                foreach (explode(",", $this->moduleHelper->getDebugEmail()) as $emailAddress) {
                    $emailAddress = trim($emailAddress);
                    $message->addTo($emailAddress, $emailAddress);
                }
                $message->setSubject('Magento Product Export Module @ ' . $this->request->getServer('SERVER_NAME'));
                $message->setBody('Warning/Error/Message(s): ' . $this->getLogEntry()->getResultMessages());
                $this->objectManager->create('\Magento\Framework\Mail\TransportInterfaceFactory')->create(['message' => clone $message])->sendMessage();
            } catch (\Exception $e) {
                $this->getLogEntry()->addResultMessage('Exception: ' . $e->getMessage());
                $this->getLogEntry()->setResult(Log::RESULT_WARNING);
                $this->getLogEntry()->setResultMessage($this->getLogEntry()->getResultMessages());
                $this->getLogEntry()->save();
            }
        }
        return $this;
    }

    /**
     * Check module status
     *
     * @throws LocalizedException
     */
    protected function checkStatus()
    {
        if (!$this->moduleHelper->confirmEnabled(true)) {
            throw new LocalizedException(__(str_rot13('Gur Cebqhpg Rkcbeg Zbqhyr vf abg ranoyrq. Cyrnfr znxr fher lbh\'er hfvat n inyvq yvprafr xrl naq gung gur zbqhyr unf orra ranoyrq ng Flfgrz > KGRAGB Rkgrafvbaf > Cebqhpg Rkcbeg pbasvthengvba.')));
        }
    }
}