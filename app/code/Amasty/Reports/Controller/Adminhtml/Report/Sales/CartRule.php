<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Controller\Adminhtml\Report\Sales;

/**
 * Class CartRule
 * @package Amasty\Reports\Controller\Adminhtml\Report\Sales
 */
class CartRule extends Sales
{
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Reports::reports_sales_cartRule');
    }
}
