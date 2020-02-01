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

use Magento\Framework\App\Action\Context;

class Subscribe extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Plumrocket\Checkoutspage\Model\Subscribe
     */
    protected $_subscribeModel;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @var Plumrocket\Checkoutspage\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @param Context                                   $context        
     * @param \Plumrocket\Checkoutspage\Model\Subscribe $subscribeModel 
     * @param \Magento\Framework\Json\Helper\Data       $jsonHelper     
     * @param \Plumrocket\Checkoutspage\Helper\Data     $helper         
     */
    public function __construct(
        Context $context,
        \Plumrocket\Checkoutspage\Model\Subscribe $subscribeModel,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Plumrocket\Checkoutspage\Helper\Data $helper
    ) {
        $this->_subscribeModel = $subscribeModel;
        $this->_dataHelper = $helper;
        $this->_jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * @return $this 
     */
    public function execute()
    {
        if ($this->getRequest()->getParam('subscribe')) {
            $result = $this->_subscribeModel->subscribe();
            $errors = $this->_subscribeModel->getErrors();

            return $this->_jsonResponse($errors);
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