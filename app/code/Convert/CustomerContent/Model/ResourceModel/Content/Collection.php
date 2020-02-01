<?php

namespace Convert\CustomerContent\Model\ResourceModel\Content;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'content_id';
    protected $_eventPrefix = 'convert_customercontent_content';
    protected $_eventObject = 'convert_customercontent_content';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Convert\CustomerContent\Model\Content', 'Convert\CustomerContent\Model\ResourceModel\Content');
    }
}
