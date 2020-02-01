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
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\Checkoutspage\Model\Config\Source;

class Nextordercoupon extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\Collection
     */
    protected $_collection;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory
     */
    protected $_ruleCollectionFactory;

    /**
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory [description]
    */
    public function __construct(
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory
    ) {
        $this->_ruleCollectionFactory = $ruleCollectionFactory;
    }

    public function getAllOptions()
    {
        if ($this->_collection === null) {
            $coupons = $this->_ruleCollectionFactory
                ->create()
                ->addFieldToFilter('is_active', 1)
                ->addFieldToFilter('coupon_type', ['neq' => \Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON])
                ->load();

            $this->_collection[] = array(
            	'label' => __('Disable'),
            	'value' => 0,
            );

            foreach ($coupons as $coupon) {
            	$this->_collection[] = array(
                    'label' => __($coupon['name']),
                    'value' => $coupon['rule_id'],
                );
            }

        }

        return $this->_collection;
    }

}