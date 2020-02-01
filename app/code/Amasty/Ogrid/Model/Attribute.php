<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */

namespace Amasty\Ogrid\Model;

class Attribute extends \Magento\Framework\Model\AbstractModel
{
    protected $_imageHelper;
    protected $_urlBuilder;
    const TABLE_ALIAS = 'ogrid_attribute_index';

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_imageHelper = $imageHelper;
        $this->_urlBuilder = $urlBuilder;

    }

    protected function _construct()
    {
        $this->_init('Amasty\Ogrid\Model\ResourceModel\Attribute');
    }

    public function getAttributeDbAlias()
    {
        return 'amasty_ogrid_product_attrubute_' . $this->getAttributeCode();
    }

    public function addFieldToSelect($collection)
    {
        $collection->getSelect()->columns([
            $this->getAttributeDbAlias() => \Amasty\Ogrid\Model\Attribute::TABLE_ALIAS . '.' . $this->getAttributeCode()
        ]);
    }

    public function modifyItem(&$item, $config = [])
    {
        if ($this->getFrontendInput() === 'media_image')
        {
            $image = [];
            $image['thumbnail'] = $item[$this->getAttributeDbAlias()];
            $product = new \Magento\Framework\DataObject($image);
            $imageHelper = $this->_imageHelper->init($product, 'product_listing_thumbnail');
            $item[$this->getAttributeDbAlias() . '_src'] = $imageHelper->getUrl();
            $origImageHelper = $this->_imageHelper->init($product, 'product_listing_thumbnail_preview');
            $item[$this->getAttributeDbAlias() . '_orig_src'] = $origImageHelper->getUrl();
            $item[$this->getAttributeDbAlias() . '_link'] = $this->_urlBuilder->getUrl(
                'sales/order/view',
                ['order_id' => $item['order_id']]
            );
        }
    }

    public function addFieldToFilter($orderItemCollection, $value)
    {
        $orderItemCollection->addFieldToFilter(\Amasty\Ogrid\Model\Attribute::TABLE_ALIAS . '.' . $this->getAttributeCode(),[
            'like' => '%'. $value . '%'
        ]);
    }

}