<?php
/*

Plumrocket Inc.

NOTICE OF LICENSE

This source file is subject to the End-user License Agreement
that is available through the world-wide-web at this URL:
http://wiki.plumrocket.net/wiki/EULA
If you are unable to obtain it through the world-wide-web, please
send an email to support@plumrocket.com so we can send you a copy immediately.

@package    Plumrocket_Base-v2.x.x
@copyright  Copyright (c) 2015-2017 Plumrocket Inc. (http://www.plumrocket.com)
@license    http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement

*/

namespace Plumrocket\Base\Helper;

class Data extends Main
{
    /**
     * @var string
     */
    protected $_configSectionId = 'plumbase';

    /**
     * Receive true if Plumrocket module is enabled
     *
     * @param  string $store
     * @return bool
     */
    public function moduleEnabled($store = null)
    {
        return true;
    }

    /**
     * Receive true admin notifications is enabled
     *
     * @return bool
     */
    public function isAdminNotificationEnabled()
    {
        $m = 'Mage_Admin'.'Not'.'ification';
        return !$this->scopeConfig->isSetFlag(
            $this->_getAd() . '/' . $m,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Receive config path
     *
     * @return string
     */
    protected function _getAd()
    {
        return 'adva'.'nced/modu'.
            'les_dis'.'able_out'.'put';
    }
}
