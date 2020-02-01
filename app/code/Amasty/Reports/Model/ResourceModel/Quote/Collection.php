<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\ResourceModel\Quote;

use Magento\Quote\Model\ResourceModel\Quote\Collection as NativeCollection;

/**
 * Class Collection
 */
class Collection extends NativeCollection
{
    /**
     * @param $from
     * @param $to
     */
    public function addDateFilter($from, $to)
    {
        $dateColumn = new \Zend_Db_Expr('IFNULL(updated_at, created_at)');

        $this->addFieldToFilter($dateColumn, ['gteq' => $from])
            ->addFieldToFilter($dateColumn, ['lt' => $to]);

        return $this;
    }

    public function addIsActiveFilter()
    {
        $this->addFieldToFilter('is_active', 1);

        return $this;
    }
}
