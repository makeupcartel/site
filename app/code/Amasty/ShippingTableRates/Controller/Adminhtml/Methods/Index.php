<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Controller\Adminhtml\Methods;

class Index extends \Amasty\ShippingTableRates\Controller\Adminhtml\Methods
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $pageResult = $this->resultPageFactory->create();
        $pageResult->getLayout();
        $pageResult->setActiveMenu('Amasty_ShippingTableRates::amstrates');
        $pageResult->addBreadcrumb(__('Shipping Table Rates'), __('Shipping Table Rates'));
        $pageResult->getConfig()->getTitle()->prepend(__('Methods '));

        return $pageResult;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_ShippingTableRates::amstrates');
    }
}
