<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Plugin\Captcha\Observer;

class CheckUserLoginObserver
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    public function __construct(\Magento\Framework\App\RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @param $subject
     * @param \Closure $closure
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return bool|mixed
     */
    public function aroundExecute($subject, \Closure $closure, \Magento\Framework\Event\Observer $observer)
    {
        return $this->request->isAjax() ? false : $closure($observer);
    }
}
