<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Controller\Product;

use Magento\Framework\App\Action\Context;

class Click extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Amasty\Xsearch\Model\ResourceModel\UserSearch\Collection
     */
    private $userSearchCollection;

    public function __construct(
        Context $context,
        \Amasty\Xsearch\Model\ResourceModel\UserSearch\Collection $userSearchCollection,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->customerSession = $customerSession;
        $this->userSearchCollection = $userSearchCollection;
        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            $this->getResponse()->setStatusHeader(403, '1.1', 'Forbidden');
            return;
        }

        $customerId = $this->customerSession->getCustomerId() ?: $this->customerSession->getSessionId();
        $userInfo = $this->userSearchCollection
            ->addFieldToFilter('user_key', $customerId)
            ->setOrder('id')
            ->setLimit(1)
            ->getFirstItem();
        if ($userInfo->getId()) {
            $userInfo->setProductClick(true)->save();
        }
    }
}
