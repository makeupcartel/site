<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Controller\Social;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;

class Accounts extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
    }

    public function getTitle()
    {
        return __('My Social Accounts');
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->initLayout();
        $navigationBlock = $resultPage->getLayout()->getBlock(
            'customer_account_navigation'
        );

        if ($navigationBlock) {
            $navigationBlock->setActive('amsociallogin/social/accounts');
        }
        $resultPage->getConfig()->getTitle()->prepend($this->getTitle());

        return $resultPage;
    }

    /**
     * Retrieve customer session object
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->customerSession;
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->_getSession()->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }
}
