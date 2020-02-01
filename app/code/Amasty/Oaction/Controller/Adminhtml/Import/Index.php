<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Oaction
 */
namespace Amasty\Oaction\Controller\Adminhtml\Import;

class Index extends \Amasty\Oaction\Controller\Adminhtml\Import
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_Oaction::tracking_import');
        $resultPage->getConfig()->getTitle()->prepend(__('Import tracking numbers'));
        $resultPage->addBreadcrumb(__('Import tracking numbers'), __('Import tracking numbers'));
        return $resultPage;
    }
}
