<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2019-04-02T08:37:07+00:00
 * File:          app/code/Xtento/ProductExport/Model/Export/Data/Product/Categories.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model\Export\Data\Product;

class Categories extends \Xtento\ProductExport\Model\Export\Data\AbstractData
{
    /**
     * Category cache
     */
    protected static $categoryCache = [];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator
     */
    protected $productUrlPathGenerator;

    /**
     * Categories constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Xtento\XtCore\Helper\Date $dateHelper
     * @param \Xtento\XtCore\Helper\Utils $utilsHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator $productUrlPathGenerator
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Xtento\XtCore\Helper\Date $dateHelper,
        \Xtento\XtCore\Helper\Utils $utilsHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator $productUrlPathGenerator,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $dateHelper, $utilsHelper, $resource, $resourceCollection, $data);
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
        $this->productUrlPathGenerator = $productUrlPathGenerator;
    }


    public function getConfiguration()
    {
        // Return config
        return [
            'name' => 'Product category information',
            'category' => 'Product',
            'description' => 'Export product categories for the given product.',
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
        $this->writeArray = & $returnArray['cats'];

        // Don't output if using "All fields in a XML file master-export-type", instead of XSL
        if ($this->getProfile()->getOutputType() == 'xml') {
            return $returnArray;
        }

        if (!$this->fieldLoadingRequired('cats')) {
            return $returnArray;
        }

        if (!isset(self::$categoryCache[$this->getStoreId()])) {
            self::$categoryCache[$this->getStoreId()] = [];
        }

        // Fetch fields to export
        $product = $collectionItem->getProduct();
        $categoryIds = $product->getCategoryIds();

        $rootCategoryId = false;
        if ($this->getStoreId()) {
            $rootCategoryId = $this->storeManager->getStore($this->getStoreId())->getRootCategoryId();
        }

        $returnArray['cats'] = [];
        $this->writeArray = & $returnArray['cats'];

        foreach ($categoryIds as $categoryId) {
            if (!array_key_exists($categoryId, self::$categoryCache[$this->getStoreId()])
                || (array_key_exists($categoryId, self::$categoryCache[$this->getStoreId()]) && !is_array(self::$categoryCache[$this->getStoreId()][$categoryId]))
            ) {
                if (array_key_exists($categoryId, self::$categoryCache[$this->getStoreId()]) && !is_array(self::$categoryCache[$this->getStoreId()][$categoryId])) {
                    $category = self::$categoryCache[$this->getStoreId()][$categoryId];
                } else {
                    try {
                        if ($this->getStoreId()) {
                            $category = $this->categoryRepository->get($categoryId, $this->getStoreId());
                        } else {
                            $category = $this->categoryRepository->get($categoryId);
                        }
                    } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                        continue;
                    }
                }

                if ($rootCategoryId > 0) {
                    if (!preg_match("/1\/" . $rootCategoryId . "\//", $category->getPath())) {
                        // Category is not associated to this root category
                        continue;
                    }
                }
                $this->writeArray = & $returnArray['cats'][];

                foreach ($category->getData() as $key => $value) {
                    $attribute = $category->getResource()->getAttribute($key);
                    $attrText = '';
                    if ($attribute && $attribute->usesSource()) {
                        $attrText = $category->getAttributeText($key);
                    }
                    if (!empty($attrText)) {
                        $this->writeValue($key, $attrText);
                    } else {
                        $this->writeValue($key, $value);
                    }
                }

                // Build category path
                $pathIds = $category->getPathIds();
                $pathAsName = "";
                foreach ($pathIds as $pathCatId) {
                    if (array_key_exists($pathCatId, self::$categoryCache[$this->getStoreId()])
                        && isset(self::$categoryCache[$this->getStoreId()][$pathCatId]['name'])
                    ) {
                        $catName = self::$categoryCache[$this->getStoreId()][$pathCatId]['name'];
                    } else {
                        try {
                            if ($this->getStoreId()) {
                                $category = $this->categoryRepository->get($pathCatId, $this->getStoreId());
                            } else {
                                $category = $this->categoryRepository->get($pathCatId);
                            }
                        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                            continue;
                        }
                        $catName = $category->getName();
                        self::$categoryCache[$this->getStoreId()][$pathCatId] = $category;
                    }
                    if (!empty($catName)) {
                        if (empty($pathAsName)) {
                            $pathAsName = $catName;
                        } else {
                            $pathAsName .= " > " . $catName;
                        }
                    }
                }
                $this->writeValue('path_name', $pathAsName);

                // Get product incl. category path URL
                $productUrl = $this->productUrlPathGenerator->getUrlPathWithSuffix($product, $this->getStoreId(), $category);
                if ($this->getProfile()->getExportUrlRemoveStore()) {
                    $productUrl = preg_replace('/(&|\?)___store=[^&]*$/', '', $productUrl);
                    $productUrl = preg_replace('/(&|\?)___store=[^&]*&/', '$1', $productUrl);
                }
                #$productUrl = $this->productUrlPathGenerator->getUrl($productUrl, array('_store' => $this->getStoreId()));
                $this->writeValue('product_url', $productUrl);

                // Cache category
                self::$categoryCache[$this->getStoreId()][$categoryId] = $this->writeArray;
            } else {
                // Copy from cache
                $this->writeArray = & $returnArray['cats'][];
                $this->writeArray = self::$categoryCache[$this->getStoreId()][$categoryId];
            }
        }

        $this->writeArray = & $returnArray;
        // Done
        return $returnArray;
    }
}