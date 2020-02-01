<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2018-06-25T12:45:07+00:00
 * File:          app/code/Xtento/ProductExport/Model/Export/Entity/Review.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model\Export\Entity;

class Review extends AbstractEntity
{
    protected $entityType = \Xtento\ProductExport\Model\Export::ENTITY_REVIEW;

    /**
     * @var \Magento\Review\Model\ResourceModel\Review\CollectionFactory
     */
    protected $reviewCollectionFactory;

    /**
     * Rating option model
     *
     * @var \Magento\Review\Model\Rating\Option\VoteFactory
     */
    protected $voteFactory;

    /**
     * Review constructor.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Xtento\ProductExport\Model\ProfileFactory $profileFactory
     * @param \Xtento\ProductExport\Model\ResourceModel\History\CollectionFactory $historyCollectionFactory
     * @param \Xtento\ProductExport\Model\Export\Data $exportData
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory
     * @param \Magento\Review\Model\Rating\Option\VoteFactory $voteFactory
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
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory,
        \Magento\Review\Model\Rating\Option\VoteFactory $voteFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->reviewCollectionFactory = $reviewCollectionFactory;
        $this->voteFactory = $voteFactory;
        parent::__construct($context, $registry, $profileFactory, $historyCollectionFactory, $exportData, $storeFactory, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $collection = $this->reviewCollectionFactory->create();

        $this->collection = $collection;
        parent::_construct();
    }

    public function runExport($forcedCollectionItem = false)
    {
        if ($this->getProfile()) {
            if ($this->getProfile()->getStoreId()) {
                $this->collection->addStoreFilter($this->getProfile()->getStoreId());
            }
        }
        $this->addRateVotes();
        return parent::runExport($forcedCollectionItem);
    }

    public function setCollectionFilters($filters)
    {
        if (is_array($filters)) {
            foreach ($filters as $filter) {
                foreach ($filter as $attribute => $filterArray) {
                    $this->collection->addFieldToFilter($attribute, $filterArray);
                }
            }
        }
        return $this->collection;
    }

    public function addRateVotes()
    {
        $storeId = $this->getProfile()->getStoreId();
        foreach ($this->collection as $item) {
            $votesCollection = $this->voteFactory->create()->getResourceCollection()->setReviewFilter(
                $item->getId()
            )->setStoreFilter(
                $storeId
            )->addRatingInfo(
                $storeId
            )->load();
            $item->setRatingVotes($votesCollection);
        }
        return $this;
    }
}