<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2017-03-06T13:48:57+00:00
 * File:          app/code/Xtento/ProductExport/Model/Export/Data.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model\Export;

use Magento\Framework\Exception\LocalizedException;

class Data extends \Magento\Framework\Model\AbstractModel
{
    protected $registeredExportData = null;
    protected $exportClassInstances = [];

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Config\DataInterface
     */
    protected $exportConfig;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Config\DataInterface $exportConfig
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Config\DataInterface $exportConfig,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->objectManager = $objectManager;
        $this->exportConfig = $exportConfig;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function getRegisteredExportData()
    {
        $this->registeredExportData = [];
        // Load registered export data
        $exportClasses = $this->exportConfig->get('classes');
        foreach ($exportClasses as $dataIdentifier => $dataConfig) {
            $profileIds = $dataConfig['profile_ids']; // Apply class only to profile IDs X,Y,Z (comma-separated)
            if ($profileIds !== false) {
                if ($this->getProfile() && in_array($this->getProfile()->getId(), explode(",", $profileIds))) {
                    $this->registeredExportData[$dataIdentifier] = $dataConfig;
                }
            } else {
                $this->registeredExportData[$dataIdentifier] = $dataConfig;
            }
        }
    }

    public function getExportData($entityType, $collectionItem = false, $getConfiguration = false)
    {
        if ($this->registeredExportData === null) {
            $this->getRegisteredExportData();
        }
        $exportData = [];
        foreach ($this->registeredExportData as $dataIdentifier => $dataConfig) {
            $className = $dataConfig['class'];
            $classIdentifier = str_replace('\Xtento\ProductExport\Model\Export\Data\\', '', $className);
            if (isset($this->exportClassInstances[$className])) {
                $exportClass = $this->exportClassInstances[$className];
            } else {
                $exportClass = $this->objectManager->create($className);
            }
            if (!isset($this->exportClassInstances[$className])) {
                $this->exportClassInstances[$className] = $exportClass;
            }
            if ($exportClass) {
                #$memBefore = memory_get_usage();
                #echo "Before - ".$exportConfig['config']->class.": $memBefore<br>";
                if ($getConfiguration) {
                    if ($exportClass->getEnabled() && $exportClass->confirmDependency() && in_array(
                            $entityType,
                            $exportClass->getApplyTo()
                        )
                    ) {
                        $exportData[] = [
                            'class' => $className,
                            'class_identifier' => $classIdentifier,
                            'configuration' => $exportClass->getConfiguration()
                        ];
                    }
                } else {
                    if (!in_array($entityType, $exportClass->getApplyTo())) {
                        continue;
                    }
                    if (!$exportClass->getEnabled() || !$exportClass->confirmDependency()) {
                        continue;
                    }
                    $returnData = $exportClass
                        ->setProfile($this->getProfile())
                        ->setShowEmptyFields($this->getShowEmptyFields())
                        ->getExportData($entityType, $collectionItem);
                    if (is_array($returnData)) {
                        $exportData = array_merge_recursive($exportData, $returnData);
                    }
                    #var_dump($className, $returnData);
                }
                #echo "After: ".memory_get_usage()." (Difference: ".round((memory_get_usage() - $memBefore) / 1024 / 1024, 2)." MB)<br>";
            }
        }
        #\Zend_Debug::dump($collectionItem); die();
        $exportData = array_merge_recursive($exportData, $this->addPrivateFields($collectionItem, $exportData));
        return $exportData;
    }

    /*
     * As data export classes are used as singletons during a single profile run, we need to reset them for each new profile exported so now old data is retained in the export classes
     */
    public function resetExportClasses()
    {
        if ($this->registeredExportData === null) {
            $this->getRegisteredExportData();
        }
        foreach ($this->registeredExportData as $dataIdentifier => $dataConfig) {
            $className = $dataConfig['class'];
            unset($this->exportClassInstances[$className]);
        }
    }

    protected function addPrivateFields($collectionItem, $exportData)
    {
        $privateFields = [];
        if ($collectionItem !== false && $collectionItem->getObject()) {
            if (!isset($exportData['entity_id'])) {
                $privateFields['entity_id'] = $collectionItem->getObject()->getId();
            }
            #if (!isset($exportData['store_id'])) {
                #$privateFields['store_id'] = $collectionItem->getObject()->getStoreId();
            #}
            if (!isset($exportData['created_at'])) {
                $privateFields['created_at'] = $collectionItem->getObject()->getCreatedAt();
            }
        }
        return $privateFields;
    }
}