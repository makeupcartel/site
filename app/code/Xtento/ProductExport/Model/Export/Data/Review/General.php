<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2019-07-03T14:59:06+00:00
 * File:          app/code/Xtento/ProductExport/Model/Export/Data/Review/General.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model\Export\Data\Review;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject;
use Xtento\ProductExport\Model\Export\Data\Product\ParentProduct;
use Xtento\ProductExport\Model\Export;

class General extends \Xtento\ProductExport\Model\Export\Data\Product\General
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var \Magento\Review\Model\ResourceModel\Review\CollectionFactory
     */
    protected $reviewCollectionFactory;

    /**
     * @var ParentProduct
     */
    protected $parentProduct;

    /**
     * General constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Xtento\XtCore\Helper\Date $dateHelper
     * @param \Xtento\XtCore\Helper\Utils $utilsHelper
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Catalog\Model\ResourceModel\Product $resourceProduct
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory $attributeSetFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Tax\Model\Calculation $taxCalculation
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory
     * @param ParentProduct $parentProduct
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Xtento\XtCore\Helper\Date $dateHelper,
        \Xtento\XtCore\Helper\Utils $utilsHelper,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Catalog\Model\ResourceModel\Product $resourceProduct,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $attributeSetFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        ProductRepositoryInterface $productRepository,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Helper\Data $catalogHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory,
        ParentProduct $parentProduct,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $dateHelper, $utilsHelper, $taxConfig, $resourceProduct, $storeManager, $attributeSetFactory, $localeDate, $productRepository, $taxCalculation, $productMetadata, $objectManager, $imageHelper, $catalogHelper, $resource, $resourceCollection, $data);

        $this->url = $urlBuilder;
        $this->reviewCollectionFactory = $reviewCollectionFactory;
        $this->parentProduct = $parentProduct;
    }

    public function getConfiguration()
    {
        return [
            'name' => 'General review information',
            'category' => 'Review',
            'description' => 'Export extended review information.',
            'enabled' => true,
            'apply_to' => [\Xtento\ProductExport\Model\Export::ENTITY_REVIEW],
        ];
    }

    // @codingStandardsIgnoreStart
    public function getExportData($entityType, $collectionItem)
    {
        // @codingStandardsIgnoreEnd
        // Set return array
        $returnArray = [];
        $this->writeArray = & $returnArray; // Write directly on review level
        // Fetch fields to export
        $review = $collectionItem->getReview();

        // Timestamps of creation/update
        if ($this->fieldLoadingRequired('created_at_timestamp')) $this->writeValue('created_at_timestamp', $this->dateHelper->convertDateToStoreTimestamp($review->getCreatedAt()));

        // Which line is this?
        $this->writeValue('line_number', $collectionItem->currItemNo);
        $this->writeValue('count', $collectionItem->collectionSize);

        // Export information
        $this->writeValue('export_id', $this->_registry->registry('productexport_log') ? $this->_registry->registry('productexport_log')->getId() : 0);

        foreach ($review->getData() as $key => $value) {
            if ($key == 'entity_id') {
                continue;
            }
            if (!$this->fieldLoadingRequired($key)) {
                continue;
            }
            $this->writeValue($key, $value);
        }

        // Add rating
        if ($this->fieldLoadingRequired('product_rating')) {
            $voteValues = [];
            foreach ($review->getRatingVotes() as $vote) {
                $voteValues[] = $vote->getValue();
            }

            $averageRating = 0;
            if (count($voteValues) > 0) {
                $averageRating = round(array_sum($voteValues) / count($voteValues), 2);
            }
            $this->writeValue('product_rating', $averageRating);
        }

        // Review link
        if ($this->fieldLoadingRequired('review_link')) {
            $reviewLink = $this->url->getUrl(
                'review/product/view', [
                'id' => $review->getReviewId(),
                '_store' => $this->getStoreId(),
                '_nosid' => true
            ]
            );
            $this->writeValue('review_link', $reviewLink);
        }

        //Total Rating Percentage & Review Count
        if ($this->fieldLoadingRequired('total_reviews')) {
            $collection = $this->reviewCollectionFactory->create()
                ->join('review_entity_summary', 'main_table.entity_pk_value = review_entity_summary.entity_pk_value and detail.store_id = review_entity_summary.store_id')
                ->addFieldToFilter('main_table.entity_pk_value', $review->getEntityPkValue())
                ->addFieldToFilter('main_table.review_id', $review->getReviewId())
                ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED);
            if ($this->getStoreId()) {
                $collection->addStoreFilter($this->getStoreId());
            }
            if ($collection->count() > 0) {
                $this->writeValue('total_product_rating_percentage', $collection->getColumnValues('rating_summary')[0]);
                $this->writeValue('total_reviews', $collection->getColumnValues('reviews_count')[0]);
            }
        }

        $originalWriteArray = & $this->writeArray;
        // Add product information
        $productId = $review->getEntityPkValue();
        if ($productId > 0) {
            try {
                $product = $this->productRepository->getById($productId, false, $this->getStoreId() ? $this->getStoreId() : null);
                if ($product->getId()) {
                    $this->writeArray = & $returnArray['product'];
                    $this->exportProductData($product, $this->writeArray);
                    $this->writeValue('entity_id', $product->getId());
                    $this->writeArray = & $originalWriteArray;
                    // Add parent item
                    if ($this->fieldLoadingRequired('parent_item')) {
                        // Export categories for parent product
                        $fakedCollectionItem = new DataObject();
                        $fakedCollectionItem->setProduct($product);
                        $exportClass = $this->parentProduct;
                        $exportClass->setProfile($this->getProfile());
                        $exportClass->setShowEmptyFields($this->getShowEmptyFields());
                        $returnData = $exportClass->getExportData(Export::ENTITY_PRODUCT, $fakedCollectionItem);
                        if (is_array($returnData) && !empty($returnData)) {
                            $this->writeArray = array_merge_recursive($this->writeArray, $returnData);
                        }
                    }
                    // Add child items
                    if ($this->fieldLoadingRequired('child_products')) {
                        // Export categories for parent product
                        $fakedCollectionItem = new DataObject();
                        $fakedCollectionItem->setProduct($product);
                        $exportClass = $this->objectManager->get('\Xtento\ProductExport\Model\Export\Data\Product\Children'); // Singleton
                        $exportClass->setProfile($this->getProfile());
                        $exportClass->setShowEmptyFields($this->getShowEmptyFields());
                        $returnData = $exportClass->getExportData(Export::ENTITY_PRODUCT, $fakedCollectionItem);
                        if (is_array($returnData) && !empty($returnData)) {
                            $this->writeArray = array_merge_recursive($this->writeArray, $returnData);
                        }
                    }
                }
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {}
        }


        // Done
        return $returnArray;
    }
}