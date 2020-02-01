<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2019-08-23T08:49:02+00:00
 * File:          app/code/Xtento/ProductExport/Model/Export/Data/Product/Children.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model\Export\Data\Product;

use Magento\Framework\DataObject;
use Xtento\ProductExport\Model\Export;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Children extends General
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * Children constructor.
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
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
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
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $dateHelper,
            $utilsHelper,
            $taxConfig,
            $resourceProduct,
            $storeManager,
            $attributeSetFactory,
            $localeDate,
            $productRepository,
            $taxCalculation,
            $productMetadata,
            $objectManager,
            $imageHelper,
            $catalogHelper,
            $resource,
            $resourceCollection,
            $data
        );
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
        $this->resourceConnection = $resourceConnection;
    }


    public function getConfiguration()
    {
        // Return config
        return [
            'name' => 'Child product information',
            'category' => 'Product',
            'description' => 'Export child products of configurable products',
            'enabled' => true,
            'apply_to' => [\Xtento\ProductExport\Model\Export::ENTITY_PRODUCT],
        ];
    }

    // @codingStandardsIgnoreStart
    public function getExportData($entityType, $collectionItem)
    {
        // @codingStandardsIgnoreEnd
        // Set return array
        $returnArray = [];
        $returnArray['child_products'] = [];

        // Fetch product - should be a "parent" item
        $product = $collectionItem->getProduct();
        if ($product->getTypeId() !== \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE 
            && $product->getTypeId() !== \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE 
            && $product->getTypeId() !== \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            return $returnArray;
        }

        $exportAllFields = false;
        if ($this->getProfile()->getOutputType() == 'xml') {
            $exportAllFields = true;
        }

        // Find & export child item
        if ($this->fieldLoadingRequired('child_products') && !$exportAllFields) {
            $originalWriteArray = & $this->writeArray;
            $this->writeArray = & $returnArray['child_products']; // Write on child_item level

            if ($product->getTypeId() === \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                $childProducts = $product->getTypeInstance()->getUsedProductCollection($product);
                if ($this->fieldLoadingRequired('child_price')) {
                    $childPrices = [];
                    $childAttributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);
                    // Loop all attributes and find out the pricing value - be aware that this could be percentage
                    foreach ($childAttributes as $childAttribute) {
                        if ($childAttribute->getPrices()) {
                            foreach ($childAttribute->getPrices() as $attributePrice) {
                                $childPrices[$attributePrice['value_index']] = $attributePrice['pricing_value'];
                            }
                        }
                    }
                }
            }
            if ($product->getTypeId() === \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE) {
                $childProducts = $product->getTypeInstance(true)->getAssociatedProductCollection($product);
            }
            if ($product->getTypeId() === \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
                $childProducts = $product->getTypeInstance(true)->getSelectionsCollection($product->getTypeInstance(true)->getOptionsIds($product), $product);
                $optionCollection = $product->getTypeInstance(true)->getOptionsCollection($product);
            }

            $childProducts->addAttributeToSelect('*');
            $childProducts->joinField('qty', 'cataloginventory_stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=1', 'left');
            $childProducts->addTaxPercents();
            if ($this->getProfile()->getStoreId()) {
                $childProducts->getSelect()->joinLeft($this->resourceConnection->getTableName(
                        'catalog_product_index_price'
                    ) . ' AS price_index',
                    'price_index.entity_id=e.entity_id AND customer_group_id=' . intval($this->getProfile()->getCustomerGroupId() ? $this->getProfile()->getCustomerGroupId() : 0) . ' AND price_index.website_id=' . $this->storeManager->getStore(
                        $this->getProfile()->getStoreId()
                    )->getWebsiteId(),
                    [
                        'min_price' => 'min_price',
                        'max_price' => 'max_price',
                        'tier_price' => 'tier_price',
                        'final_price' => 'final_price'
                    ]
                );
                $childProducts->addStoreFilter($this->getProfile()->getStoreId());
                $childProducts->setStore($this->getProfile()->getStoreId());
                $childProducts->addAttributeToSelect('tax_class_id');
            }

            foreach ($childProducts as $childProduct) {
                $this->writeArray = & $returnArray['child_products'][];
                if ($this->getStoreId()) {
                    $childProduct->setStoreId($this->getStoreId());
                }
                $this->exportProductData($childProduct, $this->writeArray);
                $this->writeValue('entity_id', $childProduct->getId());
                if ($product->getTypeId() === \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE && $this->fieldLoadingRequired('bundle_option')) {
                    $this->writeArray['bundle_option'] = [];
                    $originalWriteArray = & $this->writeArray;
                    $this->writeArray = & $this->writeArray['bundle_option'];
                    $bundleOption = $optionCollection->getItemById($childProduct->getOptionId());
                    if ($bundleOption && $bundleOption->getId()) {
                        foreach ($bundleOption->getData() as $key => $value) {
                            $this->writeValue($key, $value);
                        }
                    }
                    $this->writeArray = & $originalWriteArray;
                }
                if ($product->getTypeId() === \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE && $this->fieldLoadingRequired('child_price')) {
                    $childExtraPrice = 0;
                    foreach ($childAttributes as $childAttribute) {
                        $value = $childProduct->getData($childAttribute->getProductAttribute()->getAttributeCode());
                        foreach ($childPrices as $priceKey => $priceValue) {
                            if ($priceKey == $value) {
                                $childExtraPrice += $priceValue;
                            }
                        }
                    }
                    $this->writeValue('child_price', $product->getFinalPrice() + $childExtraPrice);
                }
                if ($this->fieldLoadingRequired('child_products/cats')) {
                    // Export categories for child product
                    $fakedCollectionItem = new DataObject();
                    $fakedCollectionItem->setProduct($childProduct);
                    $exportClass = $this->objectManager->get('\Xtento\ProductExport\Model\Export\Data\Product\Categories'); // Singleton
                    $exportClass->setProfile($this->getProfile());
                    $exportClass->setShowEmptyFields($this->getShowEmptyFields());
                    $returnData = $exportClass->getExportData(Export::ENTITY_PRODUCT, $fakedCollectionItem);
                    if (is_array($returnData) && !empty($returnData)) {
                        $this->writeArray = array_merge_recursive($this->writeArray, $returnData);
                    }
                }
                if ($this->fieldLoadingRequired('stock')) {
                    // Export stock info for child product
                    $fakedCollectionItem = new DataObject();
                    $fakedCollectionItem->setProduct($childProduct);
                    $exportClass = $this->objectManager->get('\Xtento\ProductExport\Model\Export\Data\Product\Stock'); // Singleton
                    $exportClass->setProfile($this->getProfile());
                    $exportClass->setShowEmptyFields($this->getShowEmptyFields());
                    $returnData = $exportClass->getExportData(Export::ENTITY_PRODUCT, $fakedCollectionItem);
                    if (is_array($returnData) && !empty($returnData)) {
                        $this->writeArray = array_merge_recursive($this->writeArray, $returnData);
                    }
                }
            }
            $this->writeArray = & $originalWriteArray;
        }
        $this->writeArray = & $returnArray; // Write on product level

        // Done
        return $returnArray;
    }
}