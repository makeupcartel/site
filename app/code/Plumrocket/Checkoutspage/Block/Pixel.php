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
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\Checkoutspage\Block;

class Pixel extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Plumrocket\Checkoutspage\Model\Affiliate\GoogleReview
     */
    protected $_googleReviewFactory;

    /**
     * @var Magento\Sales\Model\Order\Address
     */
    protected $_shippingAddress = null;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_date;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_currentOrder;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @var \Plumrocket\Checkoutspage\Helper\Data
     */
    protected $_helper;

    public function __construct(
        \Plumrocket\Checkoutspage\Helper\Data $helper,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_registry = $registry;
        $this->_date = $date;
        $this->_priceCurrency = $priceCurrency;
        $this->_helper = $helper;
        $this->_currentOrder = $this->_registry->registry('current_order');
        return parent::__construct($context, $data);
    }

    /**
     * Get social share message
     * @return string message
     */
    public function getAffilateCode()
    {
        $trackingCode = $this->_helper->getGoogleReviewAffiliateTrackingCode();
$this->getItemsList();
        if (!empty($trackingCode)) {
            $orderId = $this->getOrderId();
            $postCode = $this->getPostCode();
            $region = $this->getRegion();
            $city = $this->getCity();
            $country = $this->getCountryCode();
            $totalQty = $this->getTotalQty();
            $discountAmount = $this->getDiscountAmount();
            $shippingAmount = $this->getShippingAmount();
            $tax = $this->getTax();
            $itemList = $this->getItemsList();
            $currencyCode = $this->getCurrencyCode();
            $customerName = $this->getCustomerName();
            $customerEmail = $this->getCustomerEmail();
            $customerId =$this->getCustomerId();
            $subtotal = $this->getSubtotal();

            return str_replace(
                [
                    '{$orderId}',
                    '{$postCode}',
                    '{$region}',
                    '{$city}',
                    '{$country}',
                    '{$totalQty}',
                    '{$shippingAmount}',
                    '{$tax}',
                    '{$itemList}',
                    '{$itemList}',
                    '{$currencyCode}',
                    '{$customerName}',
                    '{$customerEmail}',
                    '{$customerId}',
                    '{$subtotal}'
                ],
                [
                    $orderId,
                    $postCode,
                    $region,
                    $city,
                    $country,
                    $totalQty,
                    $shippingAmount,
                    $tax,
                    $itemList,
                    $itemList,
                    $currencyCode,
                    $customerName,
                    $customerEmail,
                    $customerId,
                    $subtotal
                ],
                $trackingCode
            );
        }

        return;
    }

    public function isGoogleReviewValid()
    {
        return $this->getMerchantId()
            && $this->getOrderId()
            && $this->getCustomerEmail()
            && $this->getCountryCode()
            && $this->getDeliveryDate();
    }

    public function getMerchantId()
    {
        return $this->_helper->getMerchantId();
    }

    public function getDeliveryDate()
    {
        $deliveryDays = $this->_helper->getEstimatedDeliveryDays();
        $deliveryDays = $deliveryDays <= 0 ? 3 : $deliveryDays;

        return $this->_date->date(
            'Y-m-d',
            $this->getCreatedAt() . "+ $deliveryDays day"
        );
    }

    public function getOrderId()
    {
        return $this->getLastOrder()->getIncrementId() ?: null;
    }

    public function getCustomerId()
    {
        return $this->getLastOrder()->getCustomerId() ?: 0;
    }

    public function getCustomerEmail()
    {
        return $this->getLastOrder()->getCustomerEmail() ?: null;
    }

    public function getCustomerName()
    {
        return $this->getLastOrder()->getCustomerName() ?: null;
    }

    public function getSubtotal()
    {
        return $this->getLastOrder()->getSubtotalInvoiced() ?: null;
    }

    public function getCurrencyCode()
    {
        return $this->getLastOrder()->getOrderCurrencyCode() ?: null;
    }

    public function getItemsList()
    {
         $items = $this->getLastOrder()->getAllVisibleItems();

        foreach ($items as $item) {
            $orderItem[] = $item->getName();
        }

        if (isset($orderItem)) {
            return implode(',', $orderItem);
        }

        return '';

    }

    public function getOrderDate()
    {
        return $this->getLastOrder()->getCreatedAt() ?: null;
    }

    public function getTax()
    {
        return $this->_priceCurrency->round($this->getLastOrder()->getTaxAmount()) ?: null;
    }

    public function getShippingAmount()
    {
        return $this->_priceCurrency->round($this->getLastOrder()->getShippingAmount()) ?: null;
    }

    public function getDiscountAmount()
    {
        return $this->_priceCurrency->round($this->getLastOrder()->getDiscountAmount()) ?: null;
    }

    public function getTotalQty()
    {
        return $this->_priceCurrency->round($this->getLastOrder()->getTotalQtyOrdered()) ?: null;
    }

    public function getCountryCode()
    {
        return $this->getShippingAddress()->getCountryId() ?: null;
    }

    public function getCity()
    {
        return $this->getShippingAddress()->getCity() ?: null;
    }

    public function getRegion()
    {
        return $this->getShippingAddress()->getRegion() ?: null;
    }

    public function getPostCode()
    {
        return $this->getShippingAddress()->getPostcode() ?: null;
    }

    public function isEnabledGoogleReview()
    {
        return $this->_helper->isEnabledGoogleReview();
    }

    protected function getShippingAddress()
    {
        if ($this->_shippingAddress === null) {
            $this->_shippingAddress = $this->getLastOrder()->getShippingAddress() ?: null;
        }

        return $this->_shippingAddress;
    }

    protected function getLastOrder()
    {
        if (!$this->_currentOrder) {
            $this->_currentOrder = $this->_checkoutSession->getLastRealOrder();
        }

        return $this->_currentOrder;
    }
}
