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


namespace Plumrocket\Checkoutspage\Block;

class Registration extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $_subscriberFactory;

    /**
     * @var \Plumrocket\Checkoutspage\Helper\Data  
     */
    protected $_dataHelper;

    /**
     * @var \Magento\Customer\Model\Registration
     */
    protected $_registration;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $_accountManagement;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context           
     * @param \Magento\Checkout\Model\Session                  $checkoutSession   
     * @param \Magento\Customer\Model\Session                  $customerSession   
     * @param \Magento\Customer\Model\Registration             $registration      
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement 
     * @param \Magento\Sales\Api\OrderRepositoryInterface      $orderRepository   
     * @param \Magento\Sales\Model\Order\Address\Validator     $addressValidator  
     * @param \Magento\Newsletter\Model\SubscriberFactory      $subscriberFactory 
     * @param \Plumrocket\Checkoutspage\Helper\Data            $dataHelper 
     * @param  \Magento\Customer\Model\CustomerFactory         $customerFactory
     * @param array                                            $data              
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Registration $registration,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Order\Address\Validator $addressValidator,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Plumrocket\Checkoutspage\Helper\Data $dataHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        array $data = []
    ) {
        $this->_subscriberFactory = $subscriberFactory;
        $this->_dataHelper = $dataHelper;
        $this->_registration = $registration;
        $this->_accountManagement = $accountManagement;
        $this->_customerSession = $customerSession;
        $this->_customerFactory = $customerFactory;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve account creation url
     *
     * @return string
     */
    public function getCreateAccountUrl()
    {
        return $this->getUrl( \Plumrocket\Checkoutspage\Helper\Data::$routeName .  '/account/create');
    }

    /**
     * {@inheritdoc}
     */
    public function toHtml()
    {
        if (
            !$this->_scopeConfig->getValue(\Plumrocket\Checkoutspage\Helper\Data::$configSectionId .'/subscription/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            || !$this->_dataHelper->moduleEnabled()
        ) {
            return '';
        }

        if (!$this->isSubscribed()) {
            return parent::toHtml();
        }

        if ($this->isRegistered()) {
            return '';
        }

        return parent::toHtml();
    }

    /**
     * Is registered
     * @return boolean 
     */
    public function isRegistered()
    {

        $websiteId = $this->_storeManager->getWebsite()->getWebsiteId();
        $customer = $this->_customerFactory->create()
                ->setWebsiteId($websiteId)
                ->loadByEmail($this->getEmailAddress());

        return $this->_customerSession->isLoggedIn()
            || !$this->_registration->isAllowed()
            || $customer->getId()
            || !$this->_accountManagement->isEmailAvailable($this->getEmailAddress());
    }

    /**
     * Is customer or visitor subscriber
     * @return boolean
     */
    public function isSubscribed()
    {
        $order = $this->_dataHelper->getOrder();
        $subscriber = $this->_subscriberFactory->create()
            ->loadByEmail($order->getCustomerEmail());
        return $subscriber->getId() ? true : false;
    }

    /**
     * Retrieve account subscribe url
     * @return string
     */
    public function getSubscribeUrl()
    {
        return $this->getUrl(\Plumrocket\Checkoutspage\Helper\Data::$routeName . '/account/subscribe');
    }
}