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

namespace Plumrocket\Checkoutspage\Controller\Preview;

use Plumrocket\Checkoutspage\Helper\Data as DataHelper;

class Page extends \Plumrocket\Checkoutspage\Controller\Preview
{

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $order = $this->_getOrder();

        if (!$order || !$this->_canView()) {
            return $this->_redirectToCart();
        }

        $session = $this->getOnepage()->getCheckout();
        $session->setLastOrderId($order->getId())
            ->setLastSuccessQuoteId($order->getId())
            ->setLastQuoteId($order->getId())
            ->setLastRealOrderId($order->getIncrementId());

        $this->_customerSession->setData(DataHelper::PREVIEW_PARAM_NAME, 1);

        $this->_checkType();

        $this->_forward('success', 'onepage', 'checkout', ['preview' => true]);
    }

    /**
     * Check request typ
     * @return $this
     */
    protected function _checkType()
    {
        if ($this->getRequest()->getParam('type') == 'new' && !$this->helperData->moduleEnabled()) {
            $this->_changeModuleStatus(1);
        } elseif ($this->getRequest()->getParam('type') == 'old') {
            $this->_changeModuleStatus(0);
        }

        return $this;
    }
}
