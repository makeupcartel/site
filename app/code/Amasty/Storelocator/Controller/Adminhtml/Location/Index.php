<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Controller\Adminhtml\Location;

class Index extends \Amasty\Storelocator\Controller\Adminhtml\Location
{
    /**
     * Items list.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Amasty_Storelocator::location');
        $resultPage->getConfig()->getTitle()->prepend(__('Store Locator'));
        $resultPage->addBreadcrumb(__('Store Locator'), __('Store Locator'));
        return $resultPage;
    }
}