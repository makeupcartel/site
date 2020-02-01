<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Indexer\Data\Product;

use Amasty\ElasticSearch\Api\Data\Indexer\Data\DataMapperInterface;

class ProductDataMapper implements DataMapperInterface
{
    /**
     * @var array
     */
    private $excludedAttributes = [
        'price',
        'media_gallery',
        'tier_price',
        'quantity_and_stock_status',
        'media_gallery',
        'giftcard_amounts'
    ];

    /**
     * @var AttributeDataProvider
     */
    private $attributeDataProvider;

    /**
     * @var string[]
     */
    private $attributesExcludedFromMerge = [
        'status',
        'visibility',
        'tax_class_id'
    ];

    public function __construct(AttributeDataProvider $attributeDataProvider, array $excludedAttributes = [])
    {
        $this->attributeDataProvider = $attributeDataProvider;
        $this->excludedAttributes = array_merge($this->excludedAttributes, $excludedAttributes);
    }

    /**
     * @param array $documentData
     * @param int $storeId
     * @param array $context
     * @return array
     */
    public function map(array $documentData, $storeId, array $context = [])
    {
        // reset attribute data for new store
        $documents = [];
        foreach ($documentData as $productId => $indexData) {
            $document = ['store_id' => $storeId];
            $productIndexData = $this->getProductData($productId, $indexData, $storeId);
            foreach ($productIndexData as $attributeCode => $value) {
                if (in_array($attributeCode, $this->excludedAttributes, true)) {
                    continue;
                }
                $document[$attributeCode] = $value;
            }
            $documents[$productId] = $document;
        }

        return $documents;
    }

    /**
     * @param int $productId
     * @param array $indexData
     * @param int $storeId
     * @return array
     */
    private function getProductData($productId, array $indexData, $storeId)
    {
        $productAttributes = [self::ENTITY_ID_FIELD_NAME => $productId];
        foreach ($indexData as $attributeId => $attributeValue) {
            $attribute = $this->attributeDataProvider->getAttribute($attributeId);
            if (!$attribute) {
                continue;
            }
            $productAttributes = array_merge(
                $productAttributes,
                $this->prepareProductData(
                    $productId,
                    $attributeValue,
                    $attribute
                )
            );
        }

        return $productAttributes;
    }

    /**
     * @param int $productId
     * @param mixed $value
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     * @return array
     */
    public function prepareProductData($productId, $value, \Magento\Eav\Model\Entity\Attribute $attribute)
    {
        $productAttributes = [];
        $attributeCode = $attribute->getAttributeCode();
        $attributeFrontendInput = $attribute->getFrontendInput();
        $preparedValue = $this->getProductAttributeValue($productId, $attribute, $value);
        if (is_array($value) && in_array($attributeFrontendInput, ['select', 'multiselect'], true)) {
            $productAttributes = $this->prepareOptionalAttributeValues($attribute, $value, $preparedValue);
        } elseif ($preparedValue) {
            $productAttributes[$attributeCode] = $preparedValue;
            if (!is_array($preparedValue)) {
                $attributeOptions = $this->getAttributeOptions($attribute);
                if (isset($attributeOptions[$preparedValue])) {
                    $productAttributes[$attributeCode . '_value'] = $attributeOptions[$preparedValue];
                }

                if ($attributeFrontendInput === 'date'
                    || in_array($attribute->getBackendType(), ['datetime', 'timestamp'], true)
                ) {
                    if (preg_replace('#[ 0:-]#', '', $preparedValue) !== '') {
                        $dateObj = new \DateTime($preparedValue, new \DateTimeZone('UTC'));
                        $productAttributes[$attributeCode] = $dateObj->format('c');
                    }
                }
            }
        }

        return $productAttributes;
    }

    /**
     * @param int $productId
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     * @param mixed $attributeValue
     * @return mixed
     */
    private function getProductAttributeValue($productId, $attribute, $attributeValue)
    {
        if (is_array($attributeValue)) {
            if (isset($attributeValue[$productId])
                && (!$attribute->getIsSearchable() || $this->isAttributeExcludedFromMerge($attribute))
            ) {
                $attributeValue = $attributeValue[$productId];
            } else {
                if (in_array($attribute->getFrontendInput(), ['text', 'textarea'], true)) {
                    $attributeValue = [array_shift($attributeValue)];
                }

                $attributeValue = array_values(array_unique($attributeValue));
            }
        }

        return $attributeValue;
    }

    /**
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     * @return bool
     */
    private function isAttributeExcludedFromMerge(\Magento\Eav\Model\Entity\Attribute $attribute)
    {
        $result = false;
        if (in_array($attribute->getAttributeCode(), $this->attributesExcludedFromMerge, true)
            || in_array($attribute->getFrontendInput(), ['text', 'textarea'], true)) {
            $result = true;
        }

        return $result;
    }

    /**
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     * @param mixed $value
     * @param mixed $preparedValue
     * @return array
     */
    private function prepareOptionalAttributeValues(
        \Magento\Eav\Model\Entity\Attribute $attribute,
        $value,
        $preparedValue = null
    ) {
        /**
         * @TODO add prices mapper
         */
        $productAttributes = [];
        $attributeCode = $attribute->getAttributeCode();
        $attributeFrontendInput = $attribute->getFrontendInput();
        $attributeOptions = $this->getAttributeOptions($attribute);
        $textAttributeValue = [];
        if ($attributeFrontendInput == 'select') {
            $productAttributes[$attributeCode] = $preparedValue;
            if ($attribute->getIsSearchable()) {
                foreach ($value as $valueItem) {
                    if (isset($attributeOptions[$valueItem])) {
                        $textAttributeValue[] = $attributeOptions[$valueItem];
                    }
                }
            }
        } elseif ($attributeFrontendInput == 'multiselect') {
            $preparedValue = [];
            foreach ($value as $valueByAttribute) {
                $valueByAttribute = explode(',', $valueByAttribute);
                foreach ($valueByAttribute as $valueItem) {
                    if (isset($attributeOptions[$valueItem])) {
                        if ($attribute->getIsSearchable()) {
                            $textAttributeValue[] = $attributeOptions[$valueItem];
                        }

                        $preparedValue[] = $valueItem;
                    }
                }
            }

            $productAttributes[$attributeCode] = array_values(array_unique($preparedValue));
        }

        if (!empty($textAttributeValue)) {
            $productAttributes[$attributeCode . '_value'] = array_values(array_unique($textAttributeValue));
        }

        return $productAttributes;
    }

    /**
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     * @return array
     */
    public function getAttributeOptions(\Magento\Eav\Model\Entity\Attribute $attribute)
    {
        return $attribute->getAttributeOptionsArray();
    }
}
