<?php

namespace Convert\CustomerContent\Model\ResourceModel\CategoryStore;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'category_id';
    protected $_eventPrefix = 'convert_customercontent_category_stores';
    protected $_eventObject = 'convert_customercontent_category_stores';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Convert\CustomerContent\Model\CategoryStore', 'Convert\CustomerContent\Model\ResourceModel\CategoryStore');
    }
}
