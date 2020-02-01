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
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\Checkoutspage\Plugin\Framework\App;

use Magento\Framework\App\Config\ScopeConfigInterface;

class ConfigPlugin
{
    const ENABLE_PATH = 'prcheckoutspage/general/enabled';
    const ENABLE_PATH_ON = 'prcheckoutspage/general/enabled_on';
    const ENABLE_PATH_OFF  ='prcheckoutspage/general/enabled_off';

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->request = $request;
    }

    public function beforeGetValue(
        \Magento\Framework\App\Config $subject,
        $path = null,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {

        if ($path == self::ENABLE_PATH) {
            if ($this->request->getParam('type') == 'new') {
                $path = self::ENABLE_PATH_ON;
            } else if ($this->request->getParam('type') == 'old') {
                $path = self::ENABLE_PATH_OFF;
            }
        }

        return [$path, $scope, $scopeCode];
    }
}
