<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Controller\Adminhtml\Report\Sales;

use Amasty\Reports\Controller\Adminhtml\Report as ReportController;
use Magento\Backend\Model\View\Result\Page;

/**
 * Class Sales
 * @package Amasty\Reports\Controller\Adminhtml\Report\Sales
 */
class Sales extends ReportController
{
    /**
     * @return Page|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->prepareResponse();

        if ($resultPage instanceof Page) {
            $resultPage->addBreadcrumb(__('Sales'), __('Sales'));
        }

        return $resultPage;
    }
}
