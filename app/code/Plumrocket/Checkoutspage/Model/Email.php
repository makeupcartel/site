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

class Email
{

    /**
     * @var \Plumrocket\Checkoutspage\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \Plumrocket\Checkoutspage\Block\Coupon
     */
    protected $_couponBlock;

    /**
     * @var \Plumrocket\Checkoutspage\Block\Facebook
     */
    protected $_facebookBlock;

    /**
     * @var \Magento\Sales\Model\Order\Address\Renderer
     */
    protected $_addressRenderer;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentHelper;

    /**
     * @var \Magento\Framework\Url
     */
    protected $_url;

    /**
     * @param \Plumrocket\Checkoutspage\Helper\Data       $dataHelper      
     * @param \Plumrocket\Checkoutspage\Block\Coupon      $couponBlock     
     * @param \Plumrocket\Checkoutspage\Block\Facebook    $facebookBlock   
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer 
     * @param \Magento\Payment\Helper\Data                $paymentHelper
     * @param \Magento\Framework\Url $url
     */
    public function __construct(
        \Plumrocket\Checkoutspage\Helper\Data $dataHelper,
        \Plumrocket\Checkoutspage\Block\Coupon $couponBlock,
        \Plumrocket\Checkoutspage\Block\Facebook $facebookBlock,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Framework\Url $url
    ) {
        $this->_dataHelper = $dataHelper;
        $this->_couponBlock = $couponBlock;
        $this->_facebookBlock = $facebookBlock;
        $this->_addressRenderer = $addressRenderer;
        $this->_paymentHelper = $paymentHelper;
        $this->_url = $url;
    }

    /**
     * Get variables for old template
     *
     * @param Magento\Sales\Model\Order $order
     * @return Array
     */
    public function getOldTemplateVariables($order)
    {
        return [
            'order' => $order,
            'billing' => $order->getBillingAddress(),
            'payment_html' => $this->_getPaymentHtml($order),
            'store' => $order->getStore(),
            'formattedShippingAddress' => $this->_getFormattedShippingAddress($order),
            'formattedBillingAddress' => $this->_getFormattedBillingAddress($order),
        ];
    }

    /**
     * Get variables for new template
     *
     * @param Magento\Sales\Model\Order $order
     * @return Array
     */
    public function getNewTemplateVariables($order)
    {

        $vars = [
            'order' => $order,
            'billing' => $order->getBillingAddress(),
            'payment_html' => $this->_getPaymentHtml($order),
            'store' => $order->getStore(),
            'formattedShippingAddress' => $this->_getFormattedShippingAddress($order),
            'formattedBillingAddress' => $this->_getFormattedBillingAddress($order),
        ];

        $this->addBetterOrderEmailVarsToOrder($vars['order']);

        return $vars;
    }

    /**
     * Add better order email vartiables to order
     * @param Magenot\Sales\Model\Order $order
     * @return  $this
     */
    public function addBetterOrderEmailVarsToOrder($order)
    {
        $vars = [];
        if ($order->hasInvoices()) {
            $invoiceId = $order->getInvoiceCollection()->getFirstItem()->getId();
            $vars['print_invoice_url'] = $this->_url->getUrl(
                \Plumrocket\Checkoutspage\Helper\Data::$routeName . '/order/printInvoice',
                [
                    'invoice_id' => $invoiceId,
                    'secret' => $this->_dataHelper->getSecretKey($invoiceId),
                    '_store' => $order->getStoreId(),
                ]
            );
        }

        //getting coupon settings
        if ($this->_couponBlock->canDisplay()) {
            $vars['coupon_code'] = $this->_couponBlock->getCoupon();
            $vars['coupon_message'] = strip_tags($this->_couponBlock->getCouponMessage());
        }

        //gettting facebook section
        if ($this->_facebookBlock->isEnabled()) {
            $vars['facebook_url'] = $this->_facebookBlock->getFacebookUrl();
        }

        $object = new \Magento\Framework\DataObject($vars);

        $order->setCspParams($object);

        return $this;
    }

    /**
     * @param Magento\Sales\Model\Order $order
     * @return string|null
     */
    protected function _getFormattedShippingAddress($order)
    {
        return $order->getIsVirtual()
            ? null
            : $this->_addressRenderer->format($order->getShippingAddress(), 'html');
    }

    /**
     * @param Magento\Sales\Model\Order $order
     * @return string|null
     */
    protected function _getFormattedBillingAddress($order)
    {
        return $this->_addressRenderer->format($order->getBillingAddress(), 'html');
    }

    /**
     * Get payment info block as html
     *
     * @param Magento\Sales\Model\Order $order
     * @return string
     */
    protected function _getPaymentHtml($order)
    {
        return $this->_paymentHelper->getInfoBlockHtml(
            $order->getPayment(),
            $order->getStoreId()
        );
    }
}
