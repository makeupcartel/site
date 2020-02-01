<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Model\ResourceModel;

use Magento\Framework\DB\Select;
use Magento\Framework\Model\AbstractModel;

class Rule extends \Magento\Rule\Model\ResourceModel\AbstractResource
{
    /**
     * @var \Magento\Framework\EntityManager\EntityManager
     */
    protected $entityManager;

    /**
     * Rule constructor.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\DataObject                     $associatedEntityMap
     * @param \Magento\Framework\EntityManager\EntityManager    $entityManager
     * @param null                                              $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\DataObject $associatedEntityMap,
        \Magento\Framework\EntityManager\EntityManager $entityManager,
        $connectionName = null
    ) {
        $this->_associatedEntitiesMap = $associatedEntityMap->getData();
        parent::__construct($context, $connectionName);
        $this->entityManager = $entityManager;
    }

    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('amasty_groupcat_rule', 'rule_id');
    }

    /**
     * @param AbstractModel $rule
     *
     * @return $this
     */
    protected function _afterDelete(AbstractModel $rule)
    {
        if ($rule->getId()) {
            $connection = $this->getConnection();
            $connection->delete(
                $this->getTable('amasty_groupcat_rule_category'),
                ['rule_id=?' => $rule->getId()]
            );
            $connection->delete(
                $this->getTable('amasty_groupcat_rule_customer_group'),
                ['rule_id=?' => $rule->getId()]
            );
            $connection->delete(
                $this->getTable('amasty_groupcat_rule_store'),
                ['rule_id=?' => $rule->getId()]
            );
            $connection->delete(
                $this->getTable('amasty_groupcat_rule_product'),
                ['rule_id=?' => $rule->getId()]
            );
            $connection->delete(
                $this->getTable('amasty_groupcat_rule_customer'),
                ['rule_id=?' => $rule->getId()]
            );
        }
        return parent::_afterDelete($rule);
    }

    /**
     * Retrieve store ids of specified rule
     *
     * @param int $ruleId
     * @return array
     */
    public function getStoreIds($ruleId)
    {
        return $this->getAssociatedEntityIds($ruleId, 'store');
    }

    /**
     * Retrieve category ids of specified rule
     *
     * @param int $ruleId
     * @return array
     */
    public function getCategoryIds($ruleId)
    {
        return $this->getAssociatedEntityIds($ruleId, 'category');
    }

    /**
     * Get active rule data based on few filters
     *
     * @param int|string $date
     * @param int        $storeId
     * @param int        $customerGroupId
     * @param int        $productId
     *
     * @return array
     */
    public function getRulesFromProduct($date, $storeId, $customerGroupId, $productId)
    {
        $select = $this->getActiveMatchesSelect($date, $storeId, $customerGroupId, $productId);

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * @param int|string $date
     * @param int        $storeId
     * @param int        $customerGroupId
     * @param int        $customerId
     *
     * @return array
     * @since 1.5.3 sql group by replaced with fetchPairs
     */
    public function getRestrictedProductIds($date, $storeId, $customerGroupId, $customerId = 0)
    {
        $select = $this->getActiveMatchesSelect($date, $storeId, $customerGroupId, $customerId);
        $select->reset($select::COLUMNS)
            ->reset($select::ORDER)
            ->columns(['product_id', 'hide_product'])
            ->order(['priority ASC', 'price_action ASC']); // reverse sorting for fetching

        // keys is productId, so fetch by pairs is alternative to group by
        $selectResult = $this->getConnection()->fetchPairs($select);

        // get only restricted product ids
        return array_keys(array_diff($selectResult, [0]));
    }

    /**
     * @param int|string $date
     * @param int        $storeId
     * @param int        $customerGroupId
     * @param int        $customerId
     * @param int|null   $productId
     *
     * @return Select
     */
    public function getActiveMatchesSelect($date, $storeId, $customerGroupId, $customerId = 0, $productId = null)
    {
        if (is_string($date)) {
            $date = strtotime($date);
        }
        
        $select = $this->getConnection()->select()
            ->from(['product' => $this->getProductMatchesTable()])
            ->where('store_id in (0,?)', $storeId)
            ->where('customer_group_enabled = 0 or customer_group_id = ?', $customerGroupId)
            ->where('from_time = 0 or from_time < ?', $date)
            ->where('to_time = 0 or to_time > ?', $date)
            ->order(['priority DESC', 'price_action DESC']);

        if ($customerId !== null) {
            $select->joinInner(
                ['customer' => $this->getCustomerMatchesTable()],
                'customer.rule_id = product.rule_id',
                ['customer_id' => 'customer.customer_id']
            )
            ->where('customer_id = ?', $customerId);
        }

        if ($productId) {
            $select->where('product_id = ?', $productId);
        }
        return $select;
    }

    /**
     * Get active rule data based on few filters
     *
     * @param int|string $date
     * @param int        $storeId
     * @param int        $customerGroupId
     * @param int        $productId
     * @param int        $customerId
     *
     * @return array
     */
    public function getOneRuleForProduct($date, $storeId, $customerGroupId, $productId, $customerId = 0)
    {
        $select = $this->getActiveMatchesSelect($date, $storeId, $customerGroupId, $customerId, $productId);

        return $this->getConnection()->fetchRow($select);
    }

    /**
     * Get Restricted Category IDs from Rule Collection
     *
     * @param Rule\Collection $collection
     *
     * @return array
     * @since 1.5.3 sql group by replaced with fetchPairs
     */
    public function getCategoryIdsFromCollection(\Amasty\Groupcat\Model\ResourceModel\Rule\Collection $collection)
    {
        // join category relation table
        $collection->addCategoryFilter();
        $select = $collection->getSelect();
        $select->reset($select::COLUMNS)
            ->reset($select::ORDER)
            ->columns(['category.category_id', 'hide_category'])
            ->order(['priority ASC']); // reverse sorting for fetching
        // keys is category_id, so fetch by pairs is alternative to group by
        $selectResult = $this->getConnection()->fetchPairs($select);
        // return only restricted category ids
        return array_keys(array_diff($selectResult, [0]));
    }

    /**
     * Left Join Customer Group Table Relation for filter
     * If Joined NULL, then rule have customer_group_enabled = 0 or rule should be excluded
     *
     * @param Select $select
     * @param int    $customerGroupId
     * @param bool   $addColumnToSelect
     *
     * @return $this
     */
    public function joinCustomerGroupFilter(
        Select $select,
        $customerGroupId,
        $addColumnToSelect = false
    ) {
        $alias = 'customer_group';
        if (array_key_exists($alias, $select->getPart($select::FROM))) {
            return $this;
        }
        $connection = $this->getConnection();
        $entityInfo = $this->_getAssociatedEntityInfo('customer_group');
        $operator = ' = ?';
        if (is_array($customerGroupId)) {
            $operator = ' IN (?)';
        }
        $columns = $addColumnToSelect ? [$entityInfo['entity_id_field']] : [];
        /**
         * main_table.customer_group_enabled = 1
         * AND (customer_group.customer_group_id = ? AND main_table.rule_id = customer_group.rule_id)
         */
        $where = 'main_table.customer_group_enabled = 1 ' . Select::SQL_AND . ' ('. $alias . '.' .
            $entityInfo['entity_id_field'] . $operator . ' ' . Select::SQL_AND . ' main_table.' .
            $entityInfo['rule_id_field'] . ' =  ' . $alias . '.' . $entityInfo['rule_id_field'] . ')';

        $select->joinLeft(
            [$alias => $this->getTable($entityInfo['associations_table'])],
            $this->getConnection()->quoteInto($where, $customerGroupId),
            $columns
        )
            /*
             * (main_table.customer_group_enabled = 1 AND customer_group.customer_group_id IS NOT NULL)
             * OR main_table.customer_group_enabled = 0
             */
            ->where(
                '(' . $connection->prepareSqlCondition('main_table.customer_group_enabled', 1) . ' ' . Select::SQL_AND .
                ' ' . $connection->prepareSqlCondition($alias . '.' . $entityInfo['entity_id_field'], ['notnull' => 1]).
                ') ' . Select::SQL_OR . ' ' . $connection->prepareSqlCondition('main_table.customer_group_enabled', 0),
                null,
                Select::TYPE_CONDITION
            );

        return $this;
    }

    /**
     * Multiply rule ids by entity ids and insert
     *
     * @param int|[] $ruleIds
     * @param int|[] $entityIds
     * @param string $entityType
     * @return $this
     */
    protected function _multiplyBunchInsert($ruleIds, $entityIds, $entityType)
    {
        if (!empty($ruleIds) && (empty($entityIds) || !count($entityIds))) {
            if (!is_array($ruleIds)) {
                $ruleIds = [(int)$ruleIds];
            }
            $entityInfo = $this->_getAssociatedEntityInfo($entityType);
            $this->getConnection()->delete(
                $this->getTable($entityInfo['associations_table']),
                $this->getConnection()->quoteInto(
                    $entityInfo['rule_id_field'] . ' IN (?)',
                    $ruleIds
                )
            );
        }

        return parent::_multiplyBunchInsert($ruleIds, $entityIds, $entityType);
    }

    /**
     * Table of Product Matches for Rule
     *
     * @return string
     */
    public function getProductMatchesTable()
    {
        return $this->getTable('amasty_groupcat_rule_product');
    }

    /**
     * Table of Customer Matches for Rule
     *
     * @return string
     */
    public function getCustomerMatchesTable()
    {
        return $this->getTable('amasty_groupcat_rule_customer');
    }

    /**
     * @param AbstractModel $object
     * @param mixed $value
     * @param string $field
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        return $this->entityManager->load($object, $value);
    }

    /**
     * @param AbstractModel $object
     * @return $this
     * @throws \Exception
     */
    public function save(AbstractModel $object)
    {
        $this->entityManager->save($object);
        return $this;
    }

    /**
     * Delete the object
     *
     * @param AbstractModel $object
     * @return $this
     * @throws \Exception
     */
    public function delete(AbstractModel $object)
    {
        $this->entityManager->delete($object);
        return $this;
    }
}
