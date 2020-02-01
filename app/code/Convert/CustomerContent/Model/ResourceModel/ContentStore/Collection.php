<?php

namespace Convert\CustomerContent\Model\ResourceModel\ContentStore;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'content_id';
    protected $_eventPrefix = 'convert_customercontent_content_stores';
    protected $_eventObject = 'convert_customercontent_content_stores';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Convert\CustomerContent\Model\ContentStore', 'Convert\CustomerContent\Model\ResourceModel\ContentStore');
    }
}
