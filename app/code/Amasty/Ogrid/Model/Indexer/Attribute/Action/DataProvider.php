<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */

namespace Amasty\Ogrid\Model\Indexer\Attribute\Action;

use Magento\Framework\App\ResourceConnection;

class DataProvider
{
    protected $resource;

    protected $connection;

    protected $actionFull;

    protected $separator = ', ';

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var ResolveProductIds
     */
    private $productIdsResolver;

    /**
     * DataProvider constructor.
     *
     * @param ResourceConnection             $resource
     * @param \Magento\Catalog\Model\Product $product
     * @param ResolveProductIds              $productIdsResolver
     */
    public function __construct(
        ResourceConnection $resource,
        \Magento\Catalog\Model\Product $product,
        ResolveProductIds $productIdsResolver
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->product = $product;
        $this->productIdsResolver = $productIdsResolver;
    }

    public function getSearchableItems(
        array $staticFields,
        $itemsIds = null,
        $lastItemId = 0,
        $limit = 100
    ) {
        $select = $this->connection->select()
            ->from(
                ['e' => $this->getTable('sales_order_item')],
                ['item_id', 'product_id', 'store_id']
            );

        $categoryExist = false;
        if (in_array('category_ids', $staticFields)) {
            $categoryExist = true;
            $select->join(
                ['category' => $this->getTable('catalog_category_product')],
                'e.product_id = category.product_id',
                'GROUP_CONCAT(category_id) as category_ids'
            );
            unset($staticFields[array_search('category_ids', $staticFields)]);
        }
        if (count($staticFields) > 0) {
            $select->join(
                ['product' => $this->getTable('catalog_product_entity')],
                'e.product_id = product.entity_id',
                $staticFields
            );
        }

        if ($itemsIds !== null) {
            $select->where('e.item_id IN (?)', $itemsIds);
        }

        $select->where('e.item_id > ?', $lastItemId)->limit($limit)->order('e.item_id');

        if ($categoryExist) {
            $select->group('e.item_id');
        }

        $result = $this->connection->fetchAll($select);

        return $result;
    }

    public function setActionFull(Full $actionFull)
    {
        $this->actionFull = $actionFull;
    }

    public function getTable($table)
    {
        return $this->resource->getTableName($table);
    }

    protected function unifyField($field, $backendType = 'varchar')
    {
        if ($backendType == 'datetime') {
            $expr = $this->connection->getDateFormatSql($field, '%Y-%m-%d %H:%i:%s');
        } else {
            $expr = $field;
        }
        return $expr;
    }

    public function getItemAttributes(
        array $productsItems,
        array $attributeTypes
    ) {
        $ifStoreValue = $this->connection->getCheckSql('t_store.value_id > 0', 't_store.value', 't_default.value');
        $result = [];
        $id = $this->product->getResource()->getLinkField();

        foreach ($productsItems as $storeId => $products) {
            if ($id == 'row_id') {
                // on EE version row_id for join tables should be used
                $productIds = $this->productIdsResolver->getRowIdsFromEntityIds(array_keys($products));
            } else {
                $productIds = $products;
            }

            $selects = [];
            foreach ($attributeTypes as $backendType => $attributeIds) {
                if ($attributeIds) {
                    $tableName = $this->getTable('catalog_product_entity_' . $backendType);
                    $selects[] = $this->connection->select()->from(
                        ['t_default' => $tableName],
                        [$id, 'attribute_id']
                    )->joinLeft(
                        ['t_store' => $tableName],
                        $this->connection->quoteInto(
                            't_default.' . $id . '=t_store.' . $id .
                            ' AND t_default.attribute_id=t_store.attribute_id' .
                            ' AND t_store.store_id = ?',
                            $storeId
                        ),
                        ['value' => $this->unifyField($ifStoreValue, $backendType)]
                    )->where(
                        't_default.store_id = ?',
                        0
                    )->where(
                        't_default.attribute_id IN (?)',
                        $attributeIds
                    )->where(
                        't_default.' . $id . ' IN (?)',
                        array_keys($productIds)
                    );
                }
            }

            if ($selects) {
                $select = $this->connection->select()->union($selects, \Magento\Framework\DB\Select::SQL_UNION_ALL);

                $query = $this->connection->query($select);
                while ($row = $query->fetch()) {
                    if ($id == 'row_id' && array_key_exists($row[$id], $productIds)) {
                        $productId = $productIds[$row[$id]];
                    } else {
                        $productId = $row[$id];
                    }

                    if (array_key_exists($productId, $products)) {
                        foreach ($products[$productId] as $itemId) {
                            $result[$itemId][$row['attribute_id']] = $row['value'];
                        }
                    }
                }
            }
        }

        return $result;
    }

    public function processAttributeValue($attribute, $value)
    {
        if (in_array($attribute->getFrontendInput(), ['select', 'multiselect'])) {
            $result = '';
        } else {
            $result = $value;
        }

        return $result;
    }

    public function prepareItemIndex($indexData, $itemData)
    {
        $storeId = $itemData['store_id'];
        $index = [];

        foreach ($indexData as $entityId => $attributeData) {
            foreach ($attributeData as $attributeId => $attributeValue) {

                $value = $this->getAttributeValue($attributeId, $attributeValue, $storeId);

                if (!empty($value)) {
                    if (isset($index[$attributeId])) {
                        $index[$attributeId][$entityId] = $value;
                    } else {
                        $index[$attributeId] = [$entityId => $value];
                    }
                }
            }
        }

        return $this->prepareEntityIndex($index, $this->separator);
    }

    protected function prepareEntityIndex($index, $separator = ' ')
    {
        $indexData = [];
        foreach ($index as $attributeId => $value) {
            $indexData[$attributeId] = is_array($value) ? implode($separator, $value) : $value;
        }
        return $indexData;
    }

    protected function getAttributeValue($attributeId, $valueId, $storeId)
    {
        $attribute = $this->actionFull->getSearchableAttribute($attributeId);

        $value = $this->processAttributeValue($attribute, $valueId);

        if ($value !== false && $attribute->usesSource()) {
            $attribute->setStoreId($storeId);
            $valueText = (array) $attribute->getSource()->getIndexOptionText($valueId);
            $value = implode($this->separator, $valueText);
        }

        $value = preg_replace('/\\s+/siu', ' ', trim(strip_tags($value)));

        return $value;
    }
}
