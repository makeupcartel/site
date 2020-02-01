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
 * Class Returning
 * @package Amasty\Reports\Controller\Adminhtml\Report\Customers
 */
class Returning extends ReportController
{
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Reports::reports_customers_returning');
    }

    /**
     * @return Page|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->prepareResponse();
        
        if ($resultPage instanceof Page) {
            $resultPage->addBreadcrumb(__('Customers'), __('Customers'));
        }

        return $resultPage;
    }
}
