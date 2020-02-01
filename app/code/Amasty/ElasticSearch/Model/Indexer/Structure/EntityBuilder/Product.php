<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Indexer\Structure\EntityBuilder;

use Amasty\ElasticSearch\Api\Data\Indexer\Structure\EntityBuilderInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

class Product implements EntityBuilderInterface
{
    const ATTRIBUTE_TYPE_TEXT       = 'text';
    const ATTRIBUTE_TYPE_KEYWORD    = 'keyword';
    const ATTRIBUTE_TYPE_FLOAT      = 'float';
    const ATTRIBUTE_TYPE_INT        = 'integer';
    const ATTRIBUTE_TYPE_DATE       = 'date';
    const DEFAULT_ATTRIBUTE_CODE_ARRAY = ['category_ids', 'visibility'];

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var array
     */
    private $customerGroupIds = [];

    public function __construct(
        Config $eavConfig,
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $customerGroupCollectionFactory
    ) {
        $this->eavConfig = $eavConfig;
        $customerGroupCollection = $customerGroupCollectionFactory->create();
        $this->customerGroupIds = $customerGroupCollection->getAllIds();
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function buildEntityFields()
    {
        $attributeCodes = $this->eavConfig->getEntityAttributeCodes(ProductAttributeInterface::ENTITY_TYPE_CODE);
        $allAttributes = [];

        foreach ($attributeCodes as $attributeCode) {
            $attribute = $this->eavConfig->getAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode);
            $allAttributes[$attributeCode] = [
                'type' => $this->getAttributeType($attribute),
            ];

            if ($attributeCode == "category_ids") {
                $allAttributes[$attributeCode] = [
                    'type' => self::ATTRIBUTE_TYPE_INT
                ];
            } elseif ($attributeCode == "sku") {
                $allAttributes[$attributeCode]['fielddata'] = true;
            }

            if ($allAttributes[$attributeCode]['type'] == self::ATTRIBUTE_TYPE_DATE) {
                $allAttributes[$attributeCode]['format'] = 'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis';
            } elseif ($attributeCode == "price") {
                foreach ($this->customerGroupIds as $groupId) {
                    $allAttributes[$attributeCode . '_' . $groupId] = ['type' => self::ATTRIBUTE_TYPE_FLOAT];
                }
            }

            if ($attribute->usesSource()
                || $attribute->getFrontendInput() === 'select'
                || $attribute->getFrontendInput() === 'multiselect'
            ) {
                $allAttributes[$attributeCode]['type'] = self::ATTRIBUTE_TYPE_KEYWORD;

                $allAttributes[$attributeCode . '_value'] = [
                    'type' => self::ATTRIBUTE_TYPE_TEXT,
                ];
            }
        }

        return $allAttributes;
    }

    /**
     * @param AbstractAttribute $attribute
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function isSearchable(AbstractAttribute $attribute)
    {
        return $attribute->getIsSearchable()
            && ($attribute->getIsVisibleInAdvancedSearch()
                || $attribute->getIsFilterable()
                || $attribute->getIsFilterableInSearch()
            )
            || (in_array($attribute->getAttributeCode(), self::DEFAULT_ATTRIBUTE_CODE_ARRAY, true));
    }

    /**
     * @param AbstractAttribute $attribute
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAttributeType(AbstractAttribute $attribute)
    {
        $backendType = $attribute->getBackendType();
        $frontendInput = $attribute->getFrontendInput();

        if ((in_array($backendType, ['int', 'smallint'], true)
                || (in_array($frontendInput, ['select', 'boolean'], true) && $backendType !== 'varchar'))
            && !$attribute->getIsUserDefined()
        ) {
            $fieldType = self::ATTRIBUTE_TYPE_INT;
        } elseif ($backendType === 'decimal') {
            $fieldType = self::ATTRIBUTE_TYPE_FLOAT;
        } else {
            if (!$this->isSearchable($attribute)) {
                if ($attribute->getIsFilterable() || $attribute->getIsFilterableInSearch()) {
                    $fieldType = self::ATTRIBUTE_TYPE_KEYWORD;
                } else {
                    $fieldType = self::ATTRIBUTE_TYPE_TEXT;
                }
            } else {
                $fieldType = self::ATTRIBUTE_TYPE_TEXT;
            }
        }

        return $fieldType;
    }
}
