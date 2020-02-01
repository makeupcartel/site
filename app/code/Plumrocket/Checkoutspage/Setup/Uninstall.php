<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Checkoutspage
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\Checkoutspage\Setup;

/* Uninstall Checkout Success Page */
class Uninstall extends \Plumrocket\Base\Setup\AbstractUninstall
{
    /**
     * Config section id
     * @var string
     */
    protected $_configSectionId = 'prcheckoutspage';

    /**
     * Tables fields
     * @var Array
     */
    protected $_tablesFields = ['sales_order' => ['next_order_promo_code']];

    /**
     * Pathes to files
     * @var Array
     */
    protected $_pathes = ['/app/code/Plumrocket/Checkoutspage'];

    /**
     * Attributes
     * @var Array
     */
    protected $_attributes = [\Magento\Catalog\Model\Product::ENTITY => ['pr_next_order_coupon']];
}
