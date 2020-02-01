<?php

namespace Convert\CustomerContent\Model\ResourceModel\ContentCategory;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'content_id';
    protected $_eventPrefix = 'convert_customercontent_content_category';
    protected $_eventObject = 'convert_customercontent_content_category';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Convert\CustomerContent\Model\ContentCategory', 'Convert\CustomerContent\Model\ResourceModel\ContentCategory');
    }
}
