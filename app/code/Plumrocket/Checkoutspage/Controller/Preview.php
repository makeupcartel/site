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

namespace Plumrocket\Checkoutspage\Controller;

use Plumrocket\Checkoutspage\Helper\Data as HelperData;
use Magento\Store\Model\ScopeInterface;

abstract class Preview extends \Magento\Checkout\Controller\Onepage
{

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * Order Factory
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * Checkoutspage Helper
     * @var HelperData
     */
    protected $helperData;

    /**
     * MutableScopeConfigInterface
     * @var \Magento\Framework\App\Config\MutableScopeConfigInterface
     */
    protected $mutableScopeConfig;

    /**
     * Preview constructor.
     *
     * @param \Magento\Framework\App\Action\Context                     $context
     * @param \Magento\Customer\Model\Session                           $customerSession
     * @param \Magento\Customer\Api\CustomerRepositoryInterface         $customerRepository
     * @param \Magento\Customer\Api\AccountManagementInterface          $accountManagement
     * @param \Magento\Framework\Registry                               $coreRegistry
     * @param \Magento\Framework\Translate\InlineInterface              $translateInline
     * @param \Magento\Framework\Data\Form\FormKey\Validator            $formKeyValidator
     * @param \Magento\Framework\App\Config\ScopeConfigInterface        $scopeConfig
     * @param \Magento\Framework\View\LayoutFactory                     $layoutFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface                $quoteRepository
     * @param \Magento\Framework\View\Result\PageFactory                $resultPageFactory
     * @param \Magento\Framework\View\Result\LayoutFactory              $resultLayoutFactory
     * @param \Magento\Framework\Controller\Result\RawFactory           $resultRawFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory          $resultJsonFactory
     * @param \Magento\Sales\Model\OrderFactory                         $orderFactory
     * @param HelperData                                                $helperData
     * @param \Magento\Framework\App\Config\MutableScopeConfigInterface $mutableScopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        HelperData $helperData,
        \Magento\Framework\App\Config\MutableScopeConfigInterface $mutableScopeConfig
    ) {
        $this->orderFactory = $orderFactory;
        $this->helperData = $helperData;
        $this->mutableScopeConfig = $mutableScopeConfig;
        parent::__construct($context,
            $customerSession,
            $customerRepository,
            $accountManagement,
            $coreRegistry,
            $translateInline,
            $formKeyValidator,
            $scopeConfig,
            $layoutFactory,
            $quoteRepository,
            $resultPageFactory,
            $resultLayoutFactory,
            $resultRawFactory,
            $resultJsonFactory
        );
    }

    /**
     * Get order by request order increment id
     * @return \Magento\Sales\Model\Order|false
     */
    protected function _getOrder()
    {
        if ($this->_order === null) {
            $incrementId = $this->getRequest()->getParam('order_id');
            if (!$incrementId) {
                $this->_order = false;
            } else {
                $this->_order = $this->orderFactory->create()
                    ->loadByIncrementId($incrementId);

                $this->_coreRegistry->register('current_order', $this->_order);

                if (!$this->_order->getId()) {
                    $this->_order = false;
                }
            }
        }
        return $this->_order;
    }

    /**
     * Redirect to cart view page
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function _redirectToCart()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('checkout/cart/index');
        return $resultRedirect;
    }

    /**
     * Can see preview page
     * @return boolean
     */
    protected function _canView()
    {
        return ($this->getRequest()->getParam('secret') == $this->helperData->getSecretKey());
    }

    /**
     * Change current enabled module value
     * @param  int $value
     * @return $this
     */
    protected function _changeModuleStatus($value)
    {
        $this->mutableScopeConfig->setValue(HelperData::$configEnablePath, $value, ScopeInterface::SCOPE_STORE, 0);
        return $this;
    }
}
