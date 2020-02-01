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
 * Class ByBrands
 * @package Amasty\Reports\Controller\Adminhtml\Report\Catalog
 */
class ByBrands extends ByAttributes
{
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Reports::reports_catalog_by_brands');
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $brand = $this->getRequest()->getParam('brand', null);
        if ($brand !== null) {
            $params = $this->getRequest()->getParam('amreports');
            $params = [
                'name' => $brand,
                'from' => isset($params['from']) ? $params['from'] : null,
                'to' => isset($params['to']) ? $params['to'] : null,
                'store' => isset($params['store']) ? $params['store'] : null
            ];
            $this->injectFilters('amasty_report_catalog_by_brands_listing', $params);
        } else {
            $this->injectFilters(
                'amasty_report_catalog_by_brands_listing',
                [
                    'from'  => null,
                    'to'    => null,
                    'store' => null
                ]
            );
        }

        return parent::execute();
    }
}
