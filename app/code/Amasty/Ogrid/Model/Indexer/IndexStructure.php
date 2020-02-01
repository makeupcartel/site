<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */


namespace Amasty\Ogrid\Model\Indexer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use \Magento\Catalog\Model\ResourceModel\ConfigFactory;
use \Magento\Eav\Model\Config as EavConfig;

class IndexStructure implements IndexStructureInterface
{
    protected $resource;
    protected $indexScopeResolver;
    protected $entityTypeId;
    protected $configFactory;
    protected $eavConfig;
    protected $attributes;
    protected $columns;
    protected $indexes;
    protected $logger;
    protected $defaultAttributesStructure = [
        'category_ids',
    ];

    protected $defaultStructure = [
        'type' => 'text',
        'unsigned' => false,
        'nullable' => true,
        'default' => null,
        'extra' => null,
        'length' => 1024
    ];

    protected $staticColumns = [
        'entity_id', 'order_item_id'
    ];

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        ResourceConnection $resource,
        IndexScopeResolver $indexScopeResolver,
        ConfigFactory $configFactory,
        EavConfig $eavConfig
    ) {
        $this->resource = $resource;
        $this->indexScopeResolver = $indexScopeResolver;
        $this->eavConfig = $eavConfig;
        $this->logger = $context->getLogger();
    }

    public function delete($index, array $dimensions = [])
    {
        $tableName = $this->indexScopeResolver->resolve($index, $dimensions);
        if ($this->resource->getConnection()->isTableExists($tableName)) {
            $this->resource->getConnection()->dropTable($tableName);
        }
    }

    public function getEntityTypeId()
    {
        if ($this->entityTypeId === null) {
            $this->entityTypeId = $this->configFactory->create()->getEntityTypeId();
        }
        return $this->entityTypeId;
    }

    public function getEntityType()
    {
        return \Magento\Catalog\Model\Product::ENTITY;
    }

    public function getAttributes(array $attributeCodes)
    {
        if ($this->attributes === null) {
            $this->attributes = [];

            $entity = $this->eavConfig->getEntityType($this->getEntityType())->getEntity();

            foreach ($attributeCodes as $attributeCode) {
                $attribute = $this->eavConfig->getAttribute(
                    $this->getEntityType(),
                    $attributeCode
                )->setEntity(
                    $entity
                );

                if ($attribute->getId()) {
                    try {
                        // check if exists source and backend model.
                        // To prevent exception when some module was disabled
                        $attribute->usesSource() && $attribute->getSource();
                        $attribute->getBackend();

                        $attribute
                            ->setFlatAddFilterableAttributes(true)
                            ->setIsFilterable(true);

                        if (in_array($attribute->getFrontendInput(), ['select', 'multiselect', 'boolean'])) {
                            $attribute->setFrontendInput('text');
                            $attribute->setBackendType('varchar');

                        }

                        if ($attribute->getData('source_model') != '') {
                            $attribute->setData('source_model', '');
                        }

                        $this->attributes[$attributeCode] = $attribute;
                    } catch (\Exception $e) {
                        $this->logger->critical($e);
                    }
                }
            }
        }
        return $this->attributes;
    }

    public function getAttributesFlatColumns(array $attributeCodes)
    {
        if ($this->columns === null) {
            $this->columns = [];
            foreach ($this->getAttributes($attributeCodes) as $attribute) {
                /** @var $attribute \Magento\Eav\Model\Entity\Attribute */
                $columns = $attribute->getFlatColumns();
                if (in_array($attribute->getAttributeCode(), $this->defaultAttributesStructure)) {
                    $columns['category_ids'] = $this->defaultStructure;
                }

                if ($columns) {
                    $this->columns = array_merge($this->columns, $columns);
                }
            }
        }

        return $this->columns;
    }

    public function getAttributesFlatIndexes(array $attributeCodes)
    {
        if ($this->indexes === null) {
            $this->indexes = [];

            foreach ($this->getAttributes($attributeCodes) as $attribute) {
                /** @var $attribute \Magento\Eav\Model\Entity\Attribute */
                $indexes = $attribute
                    ->getFlatIndexes();

                if ($indexes !== null) {
                    $this->indexes = array_merge($this->indexes, $indexes);
                }
            }
        }

        return $this->indexes;
    }

    public function create($index, array $fields, array $dimensions = [])
    {
        $tableName = $this->indexScopeResolver->resolve($index, $dimensions);

        $attributesFlatColumns = $this->getAttributesFlatColumns($fields);
        $attributesFlatIndexes = $this->getAttributesFlatIndexes($fields);

        $columns = $this->resource->getConnection()->describeTable($tableName);

        $this->dropColumns($columns, $fields, $tableName);
        $this->addColumns($attributesFlatColumns, $tableName);
        $this->addIndexes($attributesFlatIndexes, $tableName);
    }

    public function dropColumns($columns, $fields, $tableName)
    {
        foreach ($columns as $columnCode => $columnSchema) {
            if (!in_array($columnCode, array_merge($this->staticColumns, $fields))) {
                $this->resource->getConnection()->dropColumn($tableName, $columnCode);
            }
        }
    }

    public function addColumns($attributesFlatColumns, $tableName)
    {
        foreach ($attributesFlatColumns as $fieldName => $fieldProp) {
            $columnDefinition = [
                'type' => $fieldProp['type'],
                'length' => isset($fieldProp['length']) ? $fieldProp['length'] : null,
                'nullable' => isset($fieldProp['nullable']) ? (bool)$fieldProp['nullable'] : false,
                'unsigned' => isset($fieldProp['unsigned']) ? (bool)$fieldProp['unsigned'] : false,
                'default' => isset($fieldProp['default']) ? $fieldProp['default'] : false,
                'primary' => false,
                'comment' => isset($fieldProp['comment']) ? $fieldProp['comment'] : $fieldName
            ];

            $this->resource->getConnection()
                ->addColumn($tableName, $fieldName, $columnDefinition);
        }
    }

    public function addIndexes($attributesFlatIndexes, $tableName)
    {
        foreach ($attributesFlatIndexes as $indexProp) {
            $indexName = $this->resource->getConnection()->getIndexName(
                $tableName,
                $indexProp['fields'],
                $indexProp['type']
            );

            $this->resource->getConnection()->addIndex(
                $tableName,
                $indexName,
                $indexProp['fields'],
                strtolower($indexProp['type'])
            );
        }
    }

    public function getIndexedAttributes($index, array $attributesHash, array $dimensions = [])
    {
        $tableName = $this->indexScopeResolver->resolve($index, $dimensions);

        $columns = $this->resource->getConnection()->describeTable($tableName);

        foreach ($attributesHash as $attributeId => $attributeCode) {
            if (!array_key_exists($attributeCode, $columns)) {
                unset($attributesHash[$attributeId]);
            }
        }

        return $attributesHash;
    }
}
