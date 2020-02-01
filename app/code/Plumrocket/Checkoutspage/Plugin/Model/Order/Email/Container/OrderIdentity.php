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

namespace Plumrocket\Checkoutspage\Plugin\Model\Order\Email\Container;

use Magento\Sales\Model\Order\Email\Container\OrderIdentity as OriginOrderIdentity;

class OrderIdentity
{

    /**
     * @var \Plumrocket\Checkoutspage\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @param \Plumrocket\Checkoutspage\Helper\Data $dataHelper [description]
     */
    public function __construct(
        \Plumrocket\Checkoutspage\Helper\Data $dataHelper
    ) {
        $this->_dataHelper = $dataHelper;
    }

    /**
     * After get template id
     * @param  OriginOrderIdentity $subject 
     * @param string $result 
     * @return string
     */
    public function afterGetTemplateId(OriginOrderIdentity $subject, $result)
    {
        if ($this->_isEmailEnabled()) {
            return $this->_dataHelper->getConfig($this->_getPath() . '/template');
        }

        return $result;
    }

    /**
     * After get guest template id
     * @param  OriginOrderIdentity $subject
     * @param string $result 
     * @return string
     */
    public function afterGetGuestTemplateId(OriginOrderIdentity $subject, $result)
    {
        if ($this->_isEmailEnabled()) {
            return $this->_dataHelper->getConfig($this->_getPath() . '/template');
        }

        return $result;
    }

    /**
     * Is "use better email" enabled
     * @return boolean 
     */
    protected function _isEmailEnabled()
    {
        return $this->_dataHelper->getConfig($this->_getPath() . '/enabled');
    }

    /**
     * Get path to section
     * @return string 
     */
    protected function _getPath()
    {
        return $this->_dataHelper->getConfigSectionId() .   '/email';
    }
}
