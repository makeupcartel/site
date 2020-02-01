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

namespace Plumrocket\Checkoutspage\Observer;

use Magento\Framework\Event\ObserverInterface;

class AddVariablesToOrderEmail implements ObserverInterface
{

    /**
     * @var \Plumrocket\Checkoutspage\Model\Email
     */
    protected $_emailModel;

    /**
     * @var \Plumrocket\Checkoutspage\Data\Helper
     */
    protected $_dataHelper;

    /**
     * @param \Plumrocket\Checkoutspage\Helper\Data $dataHelper 
     * @param \Plumrocket\Checkoutspage\Model\Email $emailModel 
     */
    public function __construct(
        \Plumrocket\Checkoutspage\Helper\Data $dataHelper,
        \Plumrocket\Checkoutspage\Model\Email $emailModel
    ) {
        $this->_emailModel = $emailModel;
        $this->_dataHelper = $dataHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_dataHelper->getConfig(\Plumrocket\Checkoutspage\Helper\Data::$configSectionId .  '/email/enabled')
            && $this->_dataHelper->moduleEnabled()
        ) {
            $transport = $observer->getTransport();
            $order = $transport['order'];
            $this->_emailModel->addBetterOrderEmailVarsToOrder($order);
        }
    }

}