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

namespace Plumrocket\Checkoutspage\Controller\Guest;

use Plumrocket\Checkoutspage\Helper\Data as DataHelper;

class PrintAction extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $_remoteAddress;

    /**
     * @var Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /**
     * @var  \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_time;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Framework\Registry $registry
     * @param  \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_remoteAddress = $remoteAddress;
        $this->_orderFactory = $orderFactory;
        $this->_customerSession = $customerSession;
        $this->_orderConfig = $orderConfig;
        $this->_registry = $registry;
        $this->_time = $dateTime;
        parent::__construct($context);
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->_orderFactory->create()->load($orderId);

        if ($order->getId() && $this->_canViewOrder($order)) {

            $this->_registry->register('current_order', $order);

            $this->_view->loadLayout(['default', 'sales_guest_print']);

            $resultPage = $this->_resultPageFactory->create();
            $resultPage->addHandle('print');

            return $resultPage;
        } else {
            $this->_forward('noroute');
        }
    }

    /**
     * can view order
     * @param  Magento\Sales\Model\Order $order
     * @return boolean
     */
    protected function _canViewOrder($order)
    {

        if ($order->getCustomerId() && $this->_customerSession->getCustomerId()) {
            $this->_redirect('sales/order/print', ['order_id' => $order->getId()]);
        }

        $time       = $this->_time->timestamp() - 86400;

        return
            in_array($order->getStatus(), $this->_orderConfig->getVisibleOnFrontStatuses())
            && $this->checkPermission($order->getRemoteIP())
            && $order->getCreatedAt() > date('Y-m-d H:i:s', $time)
            && $this->getRequest()->getParam('order_id') == $order->getId();
    }

    protected function checkPermission($orderRemoteIP)
    {
        return
            $this->_customerSession->getData(DataHelper::PREVIEW_PARAM_NAME)
            || $orderRemoteIP == $this->_remoteAddress->getRemoteAddress();
    }
}
