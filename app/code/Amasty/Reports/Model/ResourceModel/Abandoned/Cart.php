<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\ResourceModel\Abandoned;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Cart
 */
class Cart extends AbstractDb
{
    const MAIN_TABLE = 'amasty_reports_abandoned_cart';
    const ID = 'entity_id';
    const QUOTE_ID = 'quote_id';
    const STORE_ID = 'store_id';
    const CUSTOMER_NAME = 'customer_name';
    const CUSTOMER_ID = 'customer_id';
    const COUPON_CODE = 'coupon_code';
    const STATUS = 'status';
    const CREATED_AT = 'created_at';
    const ITEMS_QTY = 'items_qty';
    const PRODUCTS = 'products';
    const GRAND_TOTAL = 'grand_total';

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, self::ID);
    }
}
