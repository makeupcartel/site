<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Convert\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons;

/**
 * Coupon codes grid
 *
 * @api
 * @since 100.0.2
 */
class Grid extends \Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid
{
	/**
     * Massaction block name
     *
     * @var string
     */
    protected $_massactionBlockName = \Convert\SalesRule\Block\Adminhtml\Widget\Grid\Massaction\Extended::class;
}