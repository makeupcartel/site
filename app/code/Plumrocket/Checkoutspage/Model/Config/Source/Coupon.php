<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket Checkoutspage v2.x.x
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\Checkoutspage\Model\Config\Source;

class Coupon implements \Magento\Framework\Option\ArrayInterface
{

    const CUSTOM_COUPON_CODE = 'custom';

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory
     */
    protected $_ruleCollectionFactory;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\Collection
     */
    protected $_collection;

    /**
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory [description]
     */
    public function __construct(
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory
    ) {
        $this->_ruleCollectionFactory = $ruleCollectionFactory;
    }

    /**
     * Get collection of coupons
     * @return \Magento\SalesRule\Model\ResourceModel\Rule\Collection
     */
    protected function _getCollection()
    {
        if ($this->_collection === null) {
            $collection = $this->_ruleCollectionFactory
                ->create()
                ->addFieldToFilter('is_active', 1)
                ->addFieldToFilter('coupon_type', ['neq'    =>  \Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON])
                ->load();

            $this->_collection = $collection->getItems();
        }
        return $this->_collection;
    }

    /**
     * Options getter
     *
     * @return Array
     */
    public function toOptionArray()
    {
        $rules[] = ['value' =>  self::CUSTOM_COUPON_CODE, 'label' =>  __('Custom Coupon Code')];
        foreach ($this->_getCollection() as $item) {
            $rules[] = [
                'value' =>  $item->getRuleId(), 'label' =>  $item->getName()
            ];
        }

        return $rules;
    }

    /**
     * Get options in "key-value" format
     *
     * @return Array
     */
    public function toArray()
    {
        $rules = [self::CUSTOM_COUPON_CODE  => __('Custom Coupon Code')];

        foreach ($this->_getCollection() as $item) {
            $rules[$item->getRuleId()] = $item->getName();
        }

        return $rules;
    }
}
