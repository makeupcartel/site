<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Controller\Adminhtml\Report\Catalog;

use Amasty\Reports\Controller\Adminhtml\Report as ReportController;
use Magento\Backend\Model\View\Result\Page;

/**
 * Class ByProduct
 * @package Amasty\Reports\Controller\Adminhtml\Report\Catalog
 */
class ByProduct extends ReportController
{
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Reports::reports_catalog_by_product');
    }

    /**
     * @return Page|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $groupId = $this->getRequest()->getParam('customer_group_id', null);
        $params = $this->getRequest()->getParam('amreports');
        $params = [
            'from' => $params['from'] ?? null,
            'to' => $params['to'] ?? null,
            'store' => $params['store'] ?? null,
            'rule' => $params['rule'] ?? null
        ];
        if ($groupId !== null) {
            $params['customer_group_id'] = (int)$groupId;
            $this->injectFilters('amasty_report_catalog_by_product_listing', $params);
        } else {
            $this->injectFilters(
                'amasty_report_catalog_by_product_listing',
                $params
            );
        }

        $resultPage = $this->prepareResponse();

        if ($resultPage instanceof Page) {
            $resultPage->addBreadcrumb(__('Catalog'), __('Catalog'));
        }

        return $resultPage;
    }
}
