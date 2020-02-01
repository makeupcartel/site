<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

namespace Amasty\Groupcat\Model\ResourceModel\Rule;

use Magento\Framework\DB\Select;

/**
 * @method \Amasty\Groupcat\Model\ResourceModel\Rule getResource()
 * @method \Amasty\Groupcat\Model\Rule[] getItems()
 */
class Collection extends \Magento\Rule\Model\ResourceModel\Rule\Collection\AbstractCollection
{
    /**
     * Collection constructor.
     *
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface    $entityFactory
     * @param \Psr\Log\LoggerInterface                                     $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface                    $eventManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface               $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb         $resource
     * @param \Magento\Framework\DataObject                                $associatedEntityMap
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DataObject $associatedEntityMap,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->_associatedEntitiesMap = $associatedEntityMap->getData();
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amasty\Groupcat\Model\Rule', 'Amasty\Groupcat\Model\ResourceModel\Rule');
    }

    protected function _afterLoad()
    {
        $this->mapAssociatedEntities('store', 'store_ids');
        $this->mapAssociatedEntities('customer_group', 'customer_group_ids');

        $this->setFlag('add_websites_to_result', false);
        return parent::_afterLoad();
    }

    public function mapCategoryIds()
    {
        $this->mapAssociatedEntities('category', 'category_ids');
    }

    /**
     * Map Associated Entities
     *
     * @param string $entityType
     * @param string $objectField
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function mapAssociatedEntities($entityType, $objectField)
    {
        if (!$this->_items) {
            return;
        }

        $entityInfo = $this->_getAssociatedEntityInfo($entityType);
        $ruleIdField = $entityInfo['rule_id_field'];
        $entityIds = $this->getColumnValues($ruleIdField);

        $select = $this->getConnection()->select()->from(
            $this->getTable($entityInfo['associations_table'])
        )->where(
            $ruleIdField . ' IN (?)',
            $entityIds
        );

        $associatedEntities = $this->getConnection()->fetchAll($select);

        foreach ($associatedEntities as $associatedEntity) {
            $item = $this->getItemByColumnValue($ruleIdField, $associatedEntity[$ruleIdField]);
            $itemAssociatedValue = $item->getData($objectField) === null ? [] : $item->getData($objectField);
            $itemAssociatedValue[] = $associatedEntity[$entityInfo['entity_id_field']];
            $item->setData($objectField, $itemAssociatedValue);
        }
    }

    /**
     * Provide support for Associated id filter
     *
     * @param string $field
     * @param null|string|array $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'store_ids') {
            return $this->addStoreFilter($condition);
        }
        if ($field == 'category_ids') {
            return $this->addCategoryFilter($condition);
        }
        if ($field == 'customer_group_ids') {
            return $this->addCustomerGroupFilter($condition);
        }

        parent::addFieldToFilter($field, $condition);
        return $this;
    }

    /**
     * Limit rules collection by specific category IDs
     *
     * @param int|int[]|\Magento\Catalog\Api\Data\CategoryInterface $categoryId
     * @return $this
     */
    public function addCategoryFilter($categoryId = null)
    {
        if ($categoryId instanceof \Magento\Catalog\Api\Data\CategoryInterface) {
            $categoryId = $categoryId->getId();
        }
        $this->addAssociatedFilter($categoryId, 'category');
        return $this;
    }

    /**
     * Limit rules collection by specific customer Group IDs
     *
     * @param int|int[] $customerGroupId
     * @param bool      $addColumnToSelect
     *
     * @return $this
     */
    public function addCustomerGroupFilter($customerGroupId, $addColumnToSelect = false)
    {
        if ($this->getFlag('is_customer_group_table_joined')) {
            return $this;
        }
        $this->getResource()->joinCustomerGroupFilter($this->getSelect(), $customerGroupId, $addColumnToSelect);

        $this->setFlag('is_customer_group_table_joined', true);

        return $this;
    }

    /**
     * Limit rules collection by specific customer Group IDs
     *
     * @param int $customerId
     *
     * @return $this
     */
    public function addCustomerIdFilter($customerId)
    {
        // "Not logged in customer" = 0
        $this->addAssociatedFilter($customerId, 'customer');
        return $this;
    }

    /**
     * Limit rules collection by specific stores
     *
     * @param int|int[]|\Magento\Store\Api\Data\StoreInterface $storeId
     * @return $this
     */
    public function addStoreFilter($storeId = null)
    {
        if ($storeId instanceof \Magento\Store\Model\Store) {
            $storeId = $storeId->getId();
        }
        // "All Store Views" = 0
        $this->addAssociatedFilter([$storeId, 0], 'store');
        return $this;
    }

    /**
     * @param string|\DateTime $date
     *
     * @return $this
     */
    public function addDateInRangeFilter($date)
    {
        $fromDate = [
            $this->_translateCondition('main_table.date_range_enabled', 0),
            $this->_translateCondition('main_table.from_date', ['null' => 1]),
            $this->_translateCondition('main_table.from_date', ['to' => $date])
        ];
        $toDate = [
            $this->_translateCondition('main_table.date_range_enabled', 0),
            $this->_translateCondition('main_table.to_date', ['null' => 1]),
            $this->_translateCondition('main_table.to_date', ['from' => $date])
        ];
        /*
         * (date_range_enabled = 0 or from_date = 0 or from_date <= $date)
         * AND (date_range_enabled = 0 or to_date = 0 or to_date >= $date)
         */
        $this->getSelect()->where(implode(' ' . Select::SQL_OR . ' ', $fromDate), null, Select::TYPE_CONDITION);
        $this->getSelect()->where(implode(' ' . Select::SQL_OR . ' ', $toDate), null, Select::TYPE_CONDITION);

        return $this;
    }

    /**
     * Find product attribute in conditions
     *
     * @param string $attributeCode
     * @return $this
     * @api
     */
    public function addAttributeInConditionFilter($attributeCode)
    {
        $match = sprintf('%%%s%%', substr(serialize(['attribute' => $attributeCode]), 5, -1));
        $this->addFieldToFilter('conditions_serialized', ['like' => $match]);

        return $this;
    }

    /**
     * Find customer attribute in actions
     *
     * @param string $attributeCode
     * @return $this
     * @api
     */
    public function addAttributeInActionFilter($attributeCode)
    {
        $match = sprintf('%%%s%%', substr(serialize(['attribute' => $attributeCode]), 5, -1));
        $this->addFieldToFilter('actions_serialized', ['like' => $match]);

        return $this;
    }

    /**
     * Add filter to rule's associated entity Ids by entity type
     *
     * @param int|int[] $entityIds
     * @param string    $entityType
     *
     * @return $this
     */
    protected function addAssociatedFilter($entityIds, $entityType)
    {
        if (!$this->getFlag('is_' . $entityType . '_table_joined')) {
            $entityInfo = $this->_getAssociatedEntityInfo($entityType);
            $this->setFlag('is_' . $entityType . '_table_joined', true);

            $where =  'main_table.' . $entityInfo['rule_id_field'] . ' = ' .
                $entityType . '.' . $entityInfo['rule_id_field'];

            if ($entityIds) {
                $operator = ' = ?';
                if (is_array($entityIds)) {
                    $operator = ' IN (?)';
                }
                $where .= ' ' . Select::SQL_AND . ' ' .  $entityType . '.' . $entityInfo['entity_id_field'] . $operator;
            }

            $this->getSelect()->join(
                [$entityType => $this->getTable($entityInfo['associations_table'])],
                $this->getConnection()->quoteInto($where, $entityIds),
                []
            );
        }
        return $this;
    }

    /**
     * Add group by id field for collection
     *
     * @return array
     */
    public function getAllIds()
    {
        $this->getSelect()->group('main_table.' . $this->getResource()->getIdFieldName());
        return parent::getAllIds();
    }
}
