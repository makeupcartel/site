<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Plugin\Quote\Model;

use Amasty\Promo\Model\Storage;
use Magento\Quote\Api\ShipmentEstimationInterface;

class ShippingMethodManagementPlugin
{
    /**
     * @var Storage
     */
    private $registry;

    public function __construct(Storage $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Avoid auto add items on estimation, only via popup
     *
     * @param ShipmentEstimationInterface $subject
     */
    public function beforeEstimateByExtendedAddress(
        ShipmentEstimationInterface $subject
    ) {
        $this->registry->setIsAutoAddAllowed(false);
    }
}
