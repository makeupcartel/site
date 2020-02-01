<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Api;

/**
 * Shipping method management interface for guest carts.
 * @api
 */
interface GuestShippingMethodManagementInterface
{
    /**
     * List applicable shipping methods for a specified quote.
     *
     * @param string $cartId The shopping cart ID.
     * @return \Amasty\ShippingTableRates\Api\Data\ShippingMethodInterface[] An array of shipping methods.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified quote does not exist.
     * @throws \Magento\Framework\Exception\StateException The shipping address is not set.
     */
    public function getList($cartId);

    /**
     * Estimate shipping
     *
     * @param string $cartId The shopping cart ID.
     * @param \Magento\Quote\Api\Data\AddressInterface $address The estimate address
     * @return \Amasty\ShippingTableRates\Api\Data\ShippingMethodInterface[] An array of shipping methods.
     */
    public function estimateByAddress($cartId, \Magento\Quote\Api\Data\AddressInterface $address);

    /**
     * Estimate shipping by address and return list of available shipping methods
     * @param mixed $cartId
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @return \Amasty\ShippingTableRates\Api\Data\ShippingMethodInterface[] An array of shipping methods
     */
    public function estimateByExtendedAddress($cartId, \Magento\Quote\Api\Data\AddressInterface $address);
}
