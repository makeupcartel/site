<?php

namespace Convert\CustomerContent\Model\ResourceModel\Category;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'category_id';
    protected $_eventPrefix = 'convert_customercontent_category';
    protected $_eventObject = 'convert_customercontent_category';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Convert\CustomerContent\Model\Category', 'Convert\CustomerContent\Model\ResourceModel\Category');
    }

    public function filterCategory($storeId)
    {
        $categoryStoreTable = $this->getTable("convert_customercontent_category_stores");
        $this->getSelect()->join($categoryStoreTable, 'main_table.category_id= ' . $categoryStoreTable.'.category_id');
        $this->getSelect()->where($categoryStoreTable.".store_id=". $storeId);
        return $this;
    }
}
