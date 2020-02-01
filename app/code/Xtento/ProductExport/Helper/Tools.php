<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2019-08-30T12:11:36+00:00
 * File:          app/code/Xtento/ProductExport/Helper/Tools.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Helper;

use Magento\Framework\ObjectManagerInterface;
use Xtento\XtCore\Helper\Utils;

class Tools extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Xtento\ProductExport\Model\ProfileFactory
     */
    protected $profileFactory;

    /**
     * @var \Xtento\ProductExport\Model\DestinationFactory
     */
    protected $destinationFactory;

    /**
     * @var Utils
     */
    protected $utilsHelper;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Tools constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Xtento\ProductExport\Model\ProfileFactory $profileFactory
     * @param \Xtento\ProductExport\Model\DestinationFactory $destinationFactory
     * @param Utils $utilsHelper
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Xtento\ProductExport\Model\ProfileFactory $profileFactory,
        \Xtento\ProductExport\Model\DestinationFactory $destinationFactory,
        Utils $utilsHelper,
        ObjectManagerInterface $objectManager
    ) {
        parent::__construct($context);
        $this->profileFactory = $profileFactory;
        $this->destinationFactory = $destinationFactory;
        $this->utilsHelper = $utilsHelper;
        $this->objectManager = $objectManager;
    }

    /**
     * @param $profileIds
     * @param $destinationIds
     *
     * @return string
     */
    public function exportSettingsAsJson($profileIds, $destinationIds)
    {
        $randIdPrefix = rand(100000, 999999);
        $exportData = [];
        $exportData['profiles'] = [];
        $exportData['destinations'] = [];
        foreach ($profileIds as $profileId) {
            $profile = $this->profileFactory->create()->load($profileId);
            if ($profile->getId()) {
                $profile->unsetData('profile_id');
                $profileDestinationIds = $profile->getData('destination_ids');
                $newDestinationIds = [];
                foreach (explode("&", $profileDestinationIds) as $destinationId) {
                    if (is_numeric($destinationId)) {
                        $newDestinationIds[] = substr($randIdPrefix . $destinationId, 0, 8);
                    }
                }
                $profile->setData('new_destination_ids', implode("&", $newDestinationIds));
                $exportData['profiles'][] = $profile->toArray();
            }
        }
        foreach ($destinationIds as $destinationId) {
            $destination = $this->destinationFactory->create()->load($destinationId);
            if ($destination->getId()) {
                $destination->setData('new_destination_id', substr($randIdPrefix . $destinationId, 0, 8));
                $destination->unsetData('password');
                $exportData['destinations'][] = $destination->toArray();
            }
        }
        return \Zend_Json::encode($exportData);
    }

    /**
     * @param $jsonData
     * @param array $addedCounter
     * @param array $updatedCounter
     * @param bool $updateByName
     * @param string $errorMessage
     *
     * @return bool
     */
    public function importSettingsFromJson($jsonData, &$addedCounter = [], &$updatedCounter = [], $updateByName = true, &$errorMessage = "")
    {
        try {
            $settingsArray = \Zend_Json::decode($jsonData);
        } catch (\Exception $e) {
            $errorMessage = __('Import failed. Decoding of JSON import format failed.');
            return false;
        }
        // In Magento 1.x and 2.0/2.1 some fields were stored serialized. Thus, we need to convert them to JSON if importing into Magento 2.2+
        $serializedToJsonConverter = false;
        if (version_compare($this->utilsHelper->getMagentoVersion(), '2.2', '>=')) {
            $serializedToJsonConverter = $this->objectManager->create('\Xtento\ProductExport\Test\SerializedToJsonDataConverter');
        }
        // Remapped destination IDs
        $remappedDestinationIds = [];
        // Process destinations
        if (isset($settingsArray['destinations'])) {
            foreach ($settingsArray['destinations'] as $destinationData) {
                if ($updateByName) {
                    $destinationCollection = $this->destinationFactory->create()->getCollection()
                        ->addFieldToFilter('type', $destinationData['type'])
                        ->addFieldToFilter('name', $destinationData['name']);
                    if ($destinationCollection->getSize() === 1) {
                        $remappedDestinationIds[$destinationData['new_destination_id']] = $destinationCollection->getFirstItem()->getId();
                        unset($destinationData['new_destination_id']);
                        $this->destinationFactory->create()->setData($destinationData)->setId($destinationCollection->getFirstItem()->getId())->save();
                        $updatedCounter['destinations']++;
                    } else {
                        $newDestination = $this->destinationFactory->create()->setData($destinationData);
                        if (isset($destinationData['new_destination_id'])) {
                            $newDestination->setId($destinationData['new_destination_id']);
                            unset($destinationData['new_destination_id']);
                            $newDestination->saveWithId();
                        } else {
                            unset($destinationData['new_destination_id']);
                            $newDestination->save();
                        }
                        $addedCounter['destinations']++;
                    }
                } else {
                    $newDestination = $this->destinationFactory->create()->setData($destinationData);
                    if (isset($destinationData['new_destination_id'])) {
                        $newDestination->setId($destinationData['new_destination_id']);
                        unset($destinationData['new_destination_id']);
                        $newDestination->saveWithId();
                    } else {
                        unset($destinationData['new_destination_id']);
                        $newDestination->save();
                    }
                    $addedCounter['destinations']++;
                }
            }
        }
        // Process profiles
        if (isset($settingsArray['profiles'])) {
            foreach ($settingsArray['profiles'] as $profileData) {
                if ($serializedToJsonConverter !== false) {
                    if (isset($profileData['conditions_serialized']))
                        $profileData['conditions_serialized'] = $serializedToJsonConverter->convert($profileData['conditions_serialized']);
                }
                // If importing a settings file from Magento >=2.2 into <=2.1, we must make sure that the "_serialized" fields are indeed serialized and not JSON
                if (version_compare($this->utilsHelper->getMagentoVersion(), '2.2', '<')) {
                    $fieldsToCheck = ['conditions_serialized'];
                    foreach ($fieldsToCheck as $fieldToCheck) {
                        if (isset($profileData[$fieldToCheck])) {
                            try {
                                $jsonData = json_decode($profileData[$fieldToCheck], true);
                            } catch (\Exception $e) {
                                $jsonData = '';
                            }
                            if (json_last_error() == JSON_ERROR_NONE) {
                                // It's json, we need to serialize it for M2.0/2.1
                                $profileData[$fieldToCheck] = serialize($jsonData);
                            }
                        }
                    }
                }
                // Begin import
                if ($updateByName) {
                    $profileCollection = $this->profileFactory->create()->getCollection()
                        ->addFieldToFilter('entity', $profileData['entity'])
                        ->addFieldToFilter('name', $profileData['name']);
                    if (isset($profileData['new_destination_ids'])) {
                        $newDestinationIds = explode("&", $profileData['new_destination_ids']);
                        $tempDestinationIds = [];
                        foreach ($newDestinationIds as $newDestinationId) {
                            if (isset($remappedDestinationIds[$newDestinationId])) {
                                $newDestinationId = $remappedDestinationIds[$newDestinationId];
                            }
                            $tempDestinationIds[] = $newDestinationId;
                        }
                        $profileData['destination_ids'] = implode("&", $newDestinationIds);
                        unset($profileData['new_destination_ids']);
                    }
                    if ($profileCollection->getSize() === 1) {
                        $this->profileFactory->create()->setData($profileData)->setId($profileCollection->getFirstItem()->getId())->save();
                        $updatedCounter['profiles']++;
                    } else {
                        $this->profileFactory->create()->setData($profileData)->save();
                        $addedCounter['profiles']++;
                    }
                } else {
                    if (isset($profileData['new_destination_ids'])) {
                        $profileData['destination_ids'] = $profileData['new_destination_ids'];
                        unset($profileData['new_destination_ids']);
                    }
                    $this->profileFactory->create()->setData($profileData)->save();
                    $addedCounter['profiles']++;
                }
            }
        }
        return true;
    }
}
