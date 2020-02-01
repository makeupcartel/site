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

namespace Plumrocket\Checkoutspage\Model;

class Subscribe
{
    /**
     * @var \Plumrocket\Checkoutspage\Helper\Data 
     */
    protected $_dataHelper;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory 
     */
    protected $_subscribeFactory;

    /**
     * @var array
     */
    protected $_errors = [];

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeInterface;

    /**
     * @param \Plumrocket\Checkoutspage\Helper\Data       $dataHelper       
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscribeFactory 
     * @param \Magento\Customer\Model\Customer            $customer         
     * @param \Magento\Store\Model\StoreManagerInterface  $storeInterface   
     */
    public function __construct(
        \Plumrocket\Checkoutspage\Helper\Data $dataHelper,
        \Magento\Newsletter\Model\SubscriberFactory $subscribeFactory,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Store\Model\StoreManagerInterface $storeInterface
    ) {
        $this->_dataHelper = $dataHelper;
        $this->_subscribeFactory = $subscribeFactory;
        $this->_customerFactory = $customer;
        $this->_storeInterface = $storeInterface;
    }

    /**
     * Subscribe by current order
     * @param int $customerId 
     * @return boolean
     */
    public function subscribe($customerId = null)
    {
        $order = $this->_dataHelper->getOrder();
        try {
            $subscriber = $this->_subscribeFactory->create();
            if ($customerId === null) {
                $customer = $this->_customerFactory
                    ->setStore($this->_storeInterface->getStore())
                    ->loadByEmail($order->getCustomerEmail());
                if ($customer->getId()) {
                    $subscriber->subscribeCustomerById($customer->getId());
                } else {
                    $subscriber->setEmail($order->getCustomerEmail());
                }
            } else {
                $subscriber->subscribeCustomerById($customerId);
            }
            $subscriber->save();
        } catch (\Exeption $e) {
            $this->_errors[] = $e->getMessage();
        }

        return false;
    }

    /**
     * Get errors
     * @return Array 
     */
    public function getErrors()
    {
        return $this->_errors;
    }
}
