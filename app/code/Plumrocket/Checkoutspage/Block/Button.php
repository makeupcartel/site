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

class Button extends Page
{
    const DEFAULT_FACEBOOK_APP_ID = '273893043469385';

    /**
     * @var string
     */
    protected $_messageVarStore = '{{store_name}}';

    /**
     * Is option enabled
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->_scopeConfig->getValue(
            \Plumrocket\Checkoutspage\Helper\Data::$configSectionId . '/social_share/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get social share message
     * @return string message
     */
    public function getMessage()
    {
        $message = $this->_scopeConfig->getValue(
            \Plumrocket\Checkoutspage\Helper\Data::$configSectionId . '/social_share/message',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $message = str_replace($this->_messageVarStore, $this->_getStoreName(), $message);
        return $message;
    }

    /**
     * Get store name
     * @return string
     */
    protected function _getStoreName()
    {
        return $this->_scopeConfig->getValue(
            'general/store_information/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get base url
     * @return string
     */
    public function getStoreUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * Get print url
     * @return string
     */
    public function getPrintUrl()
    {
        if ($this->getOrder()->getCustomerIsGuest()) {
            return $this->getUrl(\Plumrocket\Checkoutspage\Helper\Data::$routeName . '/guest/print', ['order_id' => $this->getOrder()->getId()]);
        } else {
            return $this->getUrl('sales/order/print', ['order_id' => $this->getOrder()->getId()]);
        }
    }

    /**
     * @return string
     */
    public function getFacebookAppId()
    {
        $configFacebookAppId = $this->_scopeConfig->getValue(
            \Plumrocket\Checkoutspage\Helper\Data::$configSectionId . '/social_share/facebook_application_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return ! empty($configFacebookAppId) ? $configFacebookAppId : self::DEFAULT_FACEBOOK_APP_ID;
    }
}
