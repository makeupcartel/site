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


namespace Plumrocket\Checkoutspage\Controller\Account;

use Plumrocket\Checkoutspage\Controller\Action;
use Plumrocket\Checkoutspage\Helper\Data;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Order\CustomerManagement;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class Create extends \Magento\Customer\Controller\AbstractAccount
{

    /**
     * @var Data
     */
    protected $_dataHelper;

    /**
     * @var CustomerManagement
     */
    protected $_customerManagement;

    /**
     * @var JsonHelper
     */
    protected $_jsonHelper;

    /**
     * @var Customer
     */
    protected $_customerFactory;

    /**
     * @var Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Plumrocket\Checkoutspage\Model\Subscribe
     */
    protected $_subscribeModel;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @param Context            $context
     * @param Data               $helper
     * @param CustomerManagement $customerManagement
     * @param JsonHelper         $jsonHelper
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Plumrocket\Checkoutspage\Model\Subscribe $subscribeModel
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     */
    public function __construct(
        Context $context,
        Data $helper,
        CustomerManagement $customerManagement,
        JsonHelper $jsonHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Plumrocket\Checkoutspage\Model\Subscribe $subscribeModel,
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {
        $this->_dataHelper = $helper;
        $this->_customerManagement = $customerManagement;
        $this->_jsonHelper = $jsonHelper;
        $this->_customerFactory = $customerFactory;
        $this->_subscribeModel = $subscribeModel;
        $this->_orderFactory = $orderFactory;
        $this->_customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * @return $this
     */
    public function execute()
    {

        if (!$this->_dataHelper->moduleEnabled()) {
            return;
        }

        $order = $this->_dataHelper->getOrder();
        if ($order === null) {
            $this->_jsonResponse(__("Order not exist"));
        }
        try {

            $password = $this->getRequest()->getParam('password');
            $confirmation = $this->getRequest()->getParam('password_confirmation');
            $this->_checkPassword($password, $confirmation);

            $this->_shippingFix($order);
            $account = $this->_customerManagement->create($order->getId());
            $this->_eventManager->dispatch(
                'customer_register_success',
                ['account_controller' => $this, 'customer' => $account]
            );
            $customer = $this->_customerFactory->create()->load($account->getId());
            $customer->changePassword($password)
                ->save();
            $this->_customerSession->loginById($customer->getId());

            if ((int)$this->getRequest()->getParam('subscribe')) {
                $this->_subscribeModel->subscribe($customer->getId());
            }

            $this->_orderFactory->create()
                ->load($order->getId())
                ->setCustomerIsGuest(0)
                ->setCustomerGroupId($customer->getGroupId())
                ->setCustomerFirstname($customer->getFirstname())
                ->setCustomerLastName($customer->getLastname())
                ->save();

            $this->_jsonResponse();
        } catch (ValidatorException $e) {
            $this->_jsonResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->_jsonResponse($e->getMessage());
        }
    }

    /**
     * Sometimes, when order created via pay pal, shipping address does not have last name
     * In this case shipping first name look like "John Doe"
     * If its true function try to explode first name by space and set last name, as second part of exploded string
     * Else just set "-"
     * @param  Magento\Sales\Model\Order $order
     * @return $this
     */
    protected function _shippingFix($order)
    {
        if ($order->getShippingAddress() && $order->getShippingAddress()->getLastname() === null) {
            $firstName = $order->getShippingAddress()->getFirstname();
            $name = explode(' ', $firstName);

            $lastname = !empty($name[1]) ? $name[1] : '-';

            $order->getShippingAddress()->setLastname($lastname);
            $order->save();
        }

        return $this;
    }

    /**
     * Validate password
     * @param  string $password
     * @param  string $confirmation
     * @return void
     * @throws ValidatorException
     */
    protected function _checkPassword($password, $confirmation)
    {
        if (empty($password) || empty($confirmation)) {
            throw new ValidatorException(__("Password is required"));
        }

        if ($password != $confirmation) {
            throw new ValidatorException(__('Please make sure your passwords match.'));
        }
    }

    /**
     * Compile JSON response
     *
     * @param string $error
     * @return Http
     */
    protected function _jsonResponse($error = '')
    {
        return $this->getResponse()->representJson(
            $this->_jsonHelper->jsonEncode($this->_dataHelper->getResponseData($error))
        );
    }
}
