<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Controller\Adminhtml\Attributes;

class Index extends \Amasty\Storelocator\Controller\Adminhtml\Attributes
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
        $resultPage->setActiveMenu('Amasty_Storelocator::attributes');
        $resultPage->getConfig()->getTitle()->prepend(__('Store Locator Attributes'));
        $resultPage->addBreadcrumb(__('Store Locator Attributes'), __('Store Locator Attributes'));
        return $resultPage;
    }
}