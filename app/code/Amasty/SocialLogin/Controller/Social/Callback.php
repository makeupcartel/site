<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Controller\Social;

class Callback extends \Magento\Framework\App\Action\Action
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($this->checkRequest('hauth_start', false)
            && ($this->checkRequest('error_reason', 'user_denied')
                && $this->checkRequest('error', 'access_denied')
                && $this->checkRequest('error_code', '200')
                && $this->checkRequest('hauth_done', 'Facebook')
                || ($this->checkRequest('hauth_done', 'Twitter') && $this->checkRequest('denied'))
            )
        ) {

            return $this->_redirect('customer/account');
        }

        try {
            \Hybrid_Endpoint::process();
        } catch (\Hybrid_Exception $e) {
            return $this->_redirect($this->_redirect->getRefererUrl());
        }
    }

    /**
     * @param string $key
     * @param bool $value
     * @return bool
     */
    public function checkRequest($key, $value = false)
    {
        $param = $this->getRequest()->getParam($key, false);
        if ($value) {
            return $param == $value;
        }

        return (bool)$param;
    }
}
