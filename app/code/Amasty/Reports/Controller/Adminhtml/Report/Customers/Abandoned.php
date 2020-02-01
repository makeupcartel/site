<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */



namespace Amasty\Reports\Controller\Adminhtml\Report\Customers;

use Amasty\Reports\Controller\Adminhtml\Report as ReportController;
use Magento\Backend\Model\View\Result\Page;

/**
 * Class Abandoned
 */
class Abandoned extends ReportController
{
    /**
     * @inheritdoc
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Reports::reports_customers_abandoned');
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $resultPage = $this->prepareResponse();

        if ($resultPage instanceof Page) {
            $resultPage->addBreadcrumb(__('Abandoned Carts'), __('Abandoned Carts'));
        }

        return $resultPage;
    }
}
