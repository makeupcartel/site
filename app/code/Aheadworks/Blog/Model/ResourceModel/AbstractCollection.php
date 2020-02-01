<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\ResourceModel;

/**
 * Abstract collection of all blog entities
 * @package Aheadworks\Blog\Model\ResourceModel
 */
abstract class AbstractCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'store_id') {
            return $this->addStoreFilter($condition, false);
        }
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Add store filter
     *
     * @param int|array $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!is_array($store)) {
            $store = [$store];
        }
        if ($withAdmin) {
            $store[] = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }
        $this->addFilter('store_linkage_table.store_id', ['in' => $store], 'public');
        return $this;
    }

    /**
     * Join to store linkage table if store filter is applied
     *
     * @param string $tableName
     * @param string $columnName
     * @param string $linkageColumnName
     * @return void
     */
    protected function joinStoreLinkageTable($tableName, $columnName, $linkageColumnName)
    {
        if ($this->getFilter('store_linkage_table.store_id')) {
            $select = $this->getSelect();
            $select->joinLeft(
                ['store_linkage_table' => $this->getTable($tableName)],
                'main_table.' . $columnName . ' = store_linkage_table.' . $linkageColumnName,
                []
            )
            ->group('main_table.' . $columnName);
        }
    }

    /**
     * Attach stores data to collection items
     *
     * @param string $tableName
     * @param string $columnName
     * @param string $linkageColumnName
     * @return void
     */
    protected function attachStores($tableName, $columnName, $linkageColumnName)
    {
        $ids = $this->getColumnValues($columnName);
        if (count($ids)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from(['store_linkage_table' => $this->getTable($tableName)])
                ->where('store_linkage_table.' . $linkageColumnName . ' IN (?)', $ids);
            /** @var \Magento\Framework\DataObject $item */
            $result = $connection->fetchAll($select);
            foreach ($this as $item) {
                $storeIds = [];
                $id = $item->getData($columnName);
                foreach ($result as $data) {
                    if ($data[$linkageColumnName] == $id) {
                        $storeIds[] = $data['store_id'];
                    }
                }
                $item->setData('store_ids', $storeIds);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSelectCountSql()
    {
        return parent::getSelectCountSql()
            ->reset(\Magento\Framework\DB\Select::GROUP);
    }
}
