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

class CustomerGroup implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var \Magento\Customer\Model\GroupFactory
     */
    protected $_customerGroupFactory;

    /**
     * @var \Magento\Customer\Model\Group
     */
    protected $_collection;

    /**
     * @param \Magento\Customer\Model\GroupFactory $groupFactory 
     */
    public function __construct(
        \Magento\Customer\Model\GroupFactory $groupFactory
    ) {
        $this->_customerGroupFactory = $groupFactory;
    }


    /**
     * get collection
     * @return \Magento\Customer\Model\Group
     */
    protected function _getCollection()
    {
        if ($this->_collection === null) {
            $this->_collection = $this->_customerGroupFactory->create()
                ->getCollection();
        }

        return $this->_collection;
    }

    /**
     * Options getter
     * @return array
     */
    public function toOptionArray()
    {
        $rules = [];
        foreach ($this->_getCollection() as $item) {
            $rules[] = [
                'value' =>  $item->getCustomerGroupId(), 'label' =>  $item->getCustomerGroupCode()
            ];
        }

        return $rules;
    }

    /**
     * Get options in "key-value" format
     * @return array
     */
    public function toArray()
    {
        $rules = [];

        foreach ($this->_getCollection() as $item) {
            $rules[$item->getCustomerGroupId()] = $item->getCustomerCode();
        }

        return $rules;
    }
}
