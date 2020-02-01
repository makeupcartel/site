<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2019-06-14T13:12:55+00:00
 * File:          app/code/Xtento/ProductExport/Model/Export/Entity/AbstractEntity.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model\Export\Entity;

use Magento\Framework\DataObject;

abstract class AbstractEntity extends \Magento\Framework\Model\AbstractModel
{
    protected $collection;
    protected $entityType;
    protected $exportOnlyNewFilter = false;
    protected $returnArray = [];

    /**
     * @var \Xtento\ProductExport\Model\ProfileFactory
     */
    protected $profileFactory;

    /**
     * @var \Xtento\ProductExport\Model\ResourceModel\History\CollectionFactory
     */
    protected $historyCollectionFactory;

    /**
     * @var \Xtento\ProductExport\Model\Export\Data
     */
    protected $exportDataSingleton;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $storeFactory;

    /**
     * AbstractEntity constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Xtento\ProductExport\Model\ProfileFactory $profileFactory
     * @param \Xtento\ProductExport\Model\ResourceModel\History\CollectionFactory $historyCollectionFactory
     * @param \Xtento\ProductExport\Model\Export\Data $exportData
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Xtento\ProductExport\Model\ProfileFactory $profileFactory,
        \Xtento\ProductExport\Model\ResourceModel\History\CollectionFactory $historyCollectionFactory,
        \Xtento\ProductExport\Model\Export\Data $exportData,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->profileFactory = $profileFactory;
        $this->historyCollectionFactory = $historyCollectionFactory;
        $this->exportDataSingleton = $exportData;
        $this->storeFactory = $storeFactory;
    }

    public function runExport($forcedCollectionItem = false)
    {
        return $this->_runExport($forcedCollectionItem);
    }

    protected function _runExport($forcedCollectionItem = false)
    {
        $exportFields = [];
        // Get validation profile
        /* Alternative approach if conditions check fails, we've seen this happening in Magento 1 installations, the profile conditions were simply empty and the profile needed to be loaded again: */
        $validationProfile = $this->getProfile();
        $exportConditions = $validationProfile->getData('conditions_serialized');
        if (strlen($exportConditions) > 90) {
            // Force load profile for rule validation, as it fails on some stores if the profile is not re-loaded
            $validationProfile = $this->profileFactory->create()->load($this->getProfile()->getId());
        }
        // Reset export classes
        $this->exportDataSingleton->resetExportClasses();
        // Backup original rule_data
        $origRuleData = $this->_registry->registry('rule_data');
        $ruleDataChanged = false;
        // Register rule information for catalog rules
        $storeId = 0;
        if ($this->getProfile()->getStoreId()) {
            $storeId = $this->getProfile()->getStoreId();
        }
        $productStore = $this->storeFactory->create()->load($storeId);
        if ($productStore) {
            $this->_registry->unregister('rule_data');
            $this->_registry->register(
                'rule_data',
                new DataObject(
                    [
                        'store_id' => $storeId,
                        'website_id' => $productStore->getWebsiteId(),
                        'customer_group_id' => $this->getProfile()->getCustomerGroupId() ?
                            $this->getProfile()->getCustomerGroupId() : 0, // 0 = NOT_LOGGED_IN
                    ]
                )
            );
            $ruleDataChanged = true;
        }
        $exportedIds = [];
        // Get export fields
        if ($forcedCollectionItem === false) {
            $collectionCount = null;
            $currItemNo = 1;
            $originalCollection = $this->collection;
            $originalCollection->setOrder('entity_id', 'asc'); // Fix for missing categories as supplied by Jan-Henning Rühl
            $currPage = 1;
            $lastPage = 0;
            $break = false;
            while ($break !== true) {
                $collection = clone $originalCollection;
                $collection->setPageSize(100);
                $collection->setCurPage($currPage);
                $collection->load();
                if (is_null($collectionCount)) {
                    // Note: getSize() is cached sometimes. If there are issues that getLastPageNumber is wrong, implement own getLastPageNumber/getSize function using count($collection->getItems())
                    $collectionCount = $collection->getSize();
                    $lastPage = $collection->getLastPageNumber();
                }
                if ($currPage == $lastPage) {
                    $break = true;
                }
                $currPage++;
                foreach ($collection as $collectionItem) {
                    if ($this->getProfile()->getEntity() == \Xtento\ProductExport\Model\Export::ENTITY_REVIEW) {
                        // Bug in review collection, reviews twice sometimes. Register each ID and avoid duplicates.
                        if (isset($exportedIds[$collectionItem->getId()])) {
                            continue;
                        } else {
                            $exportedIds[$collectionItem->getId()] = 1;
                        }
                    }

                    $collectionItemValidated = true;
                    $this->_eventManager->dispatch('xtento_productexport_custom_validation', [
                        'validationProfile'             => $validationProfile,
                        'collectionItem'                => $collectionItem,
                        'collectionItemValidated'       => &$collectionItemValidated,
                    ]);

                    if ($this->getExportType() == \Xtento\ProductExport\Model\Export::EXPORT_TYPE_TEST || ($collectionItemValidated && $validationProfile->validate($collectionItem))) {
                        $returnData = $this->exportData(new \Xtento\ProductExport\Model\Export\Entity\Collection\Item($collectionItem, $this->entityType, $currItemNo, $collectionCount), $exportFields);
                        if (!empty($returnData)) {
                            $this->returnArray[] = $returnData;
                            $currItemNo++;
                        }
                    }
                }
            }
        } else {
            $rawFilters = $this->getRawCollectionFilters();
            $collectionItemValidated = true;
            // Manually check collection filters against collection item as there is no real collection
            if (is_array($rawFilters)) {
                foreach ($rawFilters as $filter) {
                    foreach ($filter as $filterField => $filterCondition) {
                        $filterField = str_replace("main_table.", "", $filterField);
                        $itemData = $forcedCollectionItem->getData($filterField);
                        foreach ($filterCondition as $filterConditionType => $acceptedValues) {
                            if ($filterConditionType == 'in') {
                                if (!in_array($itemData, $acceptedValues)) {
                                    $collectionItemValidated = false;
                                    break 3;
                                }
                            }
                            // Date filters not implemented (yet?)
                            #var_dump($filterField, $itemData, $acceptedValues);
                        }
                    }
                }
            }
            // "Export only new" filter: For collections, this is joined in the \Xtento\ProductExport\Model\Export model with the exported entity collection directly. This doesn't work for direct model exports. Thus, we need to add the filter here, too.
            if ($this->exportOnlyNewFilter) {
                $historyCollection = $this->historyCollectionFactory->create();
                $historyCollection->addFieldToFilter('entity_id', $forcedCollectionItem->getData('entity_id'));
                $historyCollection->addFieldToFilter('entity', $this->getProfile()->getEntity());
                $historyCollection->addFieldToFilter('profile_id', $this->getProfile()->getId());
                if ($historyCollection->getSize() > 0) {
                    $collectionItemValidated = false;
                }
            }
            #Zend_Debug::dump($forcedCollectionItem->getData());
            #var_dump($collectionItemValidated);
            #die();
            $this->_eventManager->dispatch('xtento_productexport_custom_validation', [
                'validationProfile'             => $validationProfile,
                'collectionItem'                => $forcedCollectionItem,
                'collectionItemValidated'       => &$collectionItemValidated,
            ]);
            // If all filters pass, then export the item
            if ($this->getExportType() == \Xtento\ProductExport\Model\Export::EXPORT_TYPE_TEST || ($collectionItemValidated && $validationProfile->validate($forcedCollectionItem))) {
                $returnData = $this->exportData(new \Xtento\ProductExport\Model\Export\Entity\Collection\Item($forcedCollectionItem, $this->entityType, 1, 1), $exportFields);
                if (!empty($returnData)) {
                    $this->returnArray[] = $returnData;
                }
            }
        }
        if ($ruleDataChanged) {
            $this->_registry->unregister('rule_data');
            $this->_registry->register('rule_data', $origRuleData);
        }
        #var_dump(__FILE__, $this->returnArray); die();
        return $this->returnArray;
    }

    public function setCollectionFilters($filters)
    {
        if (is_array($filters)) {
            foreach ($filters as $filter) {
                foreach ($filter as $attribute => $filterArray) {
                    $this->collection->addAttributeToFilter($attribute, $filterArray);
                }
            }
        }
        $this->setRawCollectionFilters($filters);
        return $this->collection;
    }

    public function addExportOnlyNewFilter()
    {
        $this->exportOnlyNewFilter = true;
    }

    protected function exportData($collectionItem, $exportFields)
    {
        return $this->exportDataSingleton
            ->setShowEmptyFields($this->getShowEmptyFields())
            ->setProfile($this->getProfile() ? $this->getProfile() : new \Magento\Framework\DataObject)
            ->setExportFields($exportFields)
            ->getExportData($this->entityType, $collectionItem);
    }
}