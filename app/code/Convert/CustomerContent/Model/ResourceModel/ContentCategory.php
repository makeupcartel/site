<?php

namespace Convert\CustomerContent\Model\ResourceModel;

class ContentCategory extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Seller constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    ) {
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('convert_customercontent_content_category', 'content_id');
    }
}