<?php

namespace Convert\CustomerContent\Model;

class ContentStore extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'convert_customercontent_content_stores';

    /**
     * @var string
     */
    protected $_cacheTag = 'convert_customercontent_content_stores';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'convert_customercontent_content_stores';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init('Convert\CustomerContent\Model\ResourceModel\ContentStore');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
