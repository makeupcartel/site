<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2018-12-21T10:47:58+00:00
 * File:          app/code/Xtento/ProductExport/Model/Export/Entity/Category.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model\Export\Entity;

class Category extends AbstractEntity
{
    protected $entityType = \Xtento\ProductExport\Model\Export::ENTITY_CATEGORY;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Category constructor.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Xtento\ProductExport\Model\ProfileFactory $profileFactory
     * @param \Xtento\ProductExport\Model\ResourceModel\History\CollectionFactory $historyCollectionFactory
     * @param \Xtento\ProductExport\Model\Export\Data $exportData
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Xtento\ProductExport\Model\ProfileFactory $profileFactory,
        \Xtento\ProductExport\Model\ResourceModel\History\CollectionFactory $historyCollectionFactory,
        \Xtento\ProductExport\Model\Export\Data $exportData,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        parent::__construct($context, $registry, $profileFactory, $historyCollectionFactory, $exportData, $storeFactory, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $collection = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('*');
        $this->collection = $collection;
        parent::_construct();
    }

    public function runExport($forcedCollectionItem = false)
    {
        $previousStoreId = false;
        if ($this->getProfile()) {
            $profileStoreId = $this->getProfile()->getStoreId();
            if ($profileStoreId) {
                if ($this->storeManager->getStore()->getId() != $this->getProfile()->getStoreId()) {
                    $previousStoreId = $this->storeManager->getStore()->getId();
                    $this->storeManager->setCurrentStore($profileStoreId); // fixes catalog price rules
                }
                $rootCategory = $this->categoryRepository
                    ->get($this->storeManager->getStore($profileStoreId)->getRootCategoryId(), $profileStoreId);
                $this->collection->addAttributeToFilter('path', ['like' => $rootCategory->getPath() . '/%']);
                $this->collection->setStoreId($profileStoreId);
            }
        }
        $result = parent::runExport($forcedCollectionItem);
        if ($previousStoreId !== false) {
            $this->storeManager->setCurrentStore($previousStoreId); // Reset store back to what it was before
        }
        return $result;
    }
}