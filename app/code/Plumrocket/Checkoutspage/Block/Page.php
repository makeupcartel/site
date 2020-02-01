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

use \Magento\Store\Model;

class Page extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Plumrocket\Checkoutspage\Helper\Data
     */
    protected $_helper;

    /**
     * @param \Plumrocket\Checkoutspage\Helper\Data            $helper  
     * @param \Magento\Framework\View\Element\Template\Context $context 
     * @param array                                            $data    
     */
    public function __construct(
        \Plumrocket\Checkoutspage\Helper\Data $helper,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_helper = $helper;
    }

    /**
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->getOrder();
    }

    /**
     * get order
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_helper->getOrder();
    }

    /**
     * get settings
     * @param  string $path 
     * @return string
     */
    public function getSettings($path)
    {
        return $this->_scopeConfig->getValue(
            \Plumrocket\Checkoutspage\Helper\Data::$configSectionId . '/' . $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toHtml()
    {
        if (!$this->_helper->moduleEnabled()) {
            return '';
        }

        return parent::toHtml();
    }
}
