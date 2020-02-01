<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */

namespace Amasty\Ogrid\Model\Column\Order;

class Comment extends \Amasty\Ogrid\Model\Column\Order
{
    public function addField(\Magento\Framework\Data\Collection $collection, $mainTableAlias = 'main_table')
    {
        $alias = $this->getAlias();

        $from = $collection->getSelect()->getPart(\Zend_Db_Select::FROM);
        if (!array_key_exists($alias, $from)) {
            $collection->getSelect()->joinLeft(
                [
                    $alias => $this->_getMainTable()
                ],
                $this->_getFieldCondition($mainTableAlias),
                []
            )->group('main_table.entity_id');

            $this->dbHelper->addGroupConcatColumn(
                $collection->getSelect(),
                'amasty_ogrid_order_comments',
                $alias . '.comment'
            );
        }

        $collection->getSelect()->columns([
            $this->_alias_prefix . $this->_fieldKey => $alias . '.' . $this->_fieldKey
        ]);

        foreach ($this->_columns as $column) {
            $collection->getSelect()->columns([
                $this->_alias_prefix . $column => $alias . '.' . $column
            ]);
        }
    }
}
