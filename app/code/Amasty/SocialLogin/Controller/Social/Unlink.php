<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Controller\Social;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;

class Unlink extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Amasty\SocialLogin\Model\ResourceModel\Social\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Amasty\SocialLogin\Model\Repository\SocialRepository
     */
    private $socialRepository;

    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Amasty\SocialLogin\Model\ResourceModel\Social\CollectionFactory $collectionFactory,
        \Amasty\SocialLogin\Model\Repository\SocialRepository $socialRepository,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->collectionFactory = $collectionFactory;
        $this->socialRepository = $socialRepository;
    }

    public function execute()
    {
        $customerId = $this->customerSession->getCustomerId();
        $type = $this->getRequest()->getParam('type');
        if ($customerId) {
            try {
                $collection = $this->collectionFactory->create()
                    ->addFieldToFilter('customer_id', $customerId)
                    ->addFieldToFilter('type', $type);

                if ($collection->count()) {
                    foreach ($collection as $item) {
                        $this->socialRepository->delete($item);
                    }

                    $this->messageManager->addSuccessMessage(__('Your account was successfully unlinked.'));
                } else {
                    $this->messageManager->addErrorMessage(__('Sorry. We can`t find information about your account'));
                }
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage(__('An unspecified error occurred. Please try again later.'));
            }
        }

        $this->_redirect('amsociallogin/social/accounts');
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
