<?php

namespace Convert\CurrentOffers\Controller\Customer;

use Magento\Review\Controller\Customer as CustomerController;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Indexer
 *
 * @package Convert\CurrentOffers\Controller\Customer
 */
class Indexer extends CustomerController
{
    /**
     * Render my product reviews
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->set(__('Current Offers'));
        return $resultPage;
    }
}
