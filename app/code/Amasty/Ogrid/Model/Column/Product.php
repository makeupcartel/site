<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */

namespace Amasty\Ogrid\Model\Column;

class Product extends \Amasty\Ogrid\Model\Column
{
    protected $_alias_prefix = 'amasty_ogrid_product_';

    public function addFieldToSelect($collection)
    {
        $fromPart = $collection->getSelect()->getPart(\Magento\Framework\DB\Select::FROM);
        if ($this->_fieldKey == 'qty_available' && !isset($fromPart['amasty_stock_item_table'])) {
            $this->addStockTableToCollection($collection, 'amasty_stock_item_table');
        } else {
            $collection->getSelect()->columns([
                $this->_alias_prefix . $this->_fieldKey => $this->_fieldKey
            ]);
        }

        foreach ($this->_columns as $column) {
            $collection->getSelect()->columns([
                $this->_alias_prefix . $column => $column
            ]);
        }
    }

    public function addFieldToFilter($orderItemCollection, $value)
    {
        if (is_array($value) &&
            array_key_exists('from', $value) &&
            array_key_exists('to', $value)
        ) {
            $orderItemCollection->addFieldToFilter('main_table.' . $this->_fieldKey, [
                'between' => $value
            ]);
        } else {
            $orderItemCollection->addFieldToFilter('main_table.' . $this->_fieldKey, [
                'like' => '%'. $value . '%'
            ]);
        }
    }

    private function addStockTableToCollection($orderItemCollection, $alias)
    {
        $orderItemCollection->join(
            [$alias => $orderItemCollection->getTable('cataloginventory_stock_item')],
            $alias . '.product_id = main_table.product_id',
            $alias . '.qty as ' . $this->_alias_prefix . $this->_fieldKey
        );
    }
}
