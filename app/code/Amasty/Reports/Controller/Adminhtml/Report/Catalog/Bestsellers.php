<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Controller\Adminhtml\Report\Catalog;

/**
 * Class Bestsellers
 * @package Amasty\Reports\Controller\Adminhtml\Report\Catalog
 */
class Bestsellers extends ByProduct
{
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Reports::reports_catalog_bestsellers');
    }
}
