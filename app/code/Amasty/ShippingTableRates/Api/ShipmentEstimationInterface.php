<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amasty\ShippingTableRates\Api;

use Amasty\ShippingTableRates\Api\Data\AddressInterface;

/**
 * Interface ShipmentManagementInterface
 * @api
 */
interface ShipmentEstimationInterface
{
    /**
     * Estimate shipping by address and return list of available shipping methods
     * @param mixed $cartId
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @return \Amasty\ShippingTableRates\Api\Data\ShippingMethodInterface[] An array of shipping methods
     */
    public function estimateByExtendedAddress($cartId, \Magento\Quote\Api\Data\AddressInterface $address);
}
