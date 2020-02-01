<?php

namespace Convert\CardOnFile\Model\Payment;

use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Class CardOnFile
 *
 * @package Convert\CardOnFile\Model\Payment
 */
class CardOnFile extends AbstractMethod
{

    /**
     * Payment Method code
     *
     * @var string
     */
    protected $_code = "cardonfile";

    /**
     * Payment Method is offline
     *
     * @var bool
     */
    protected $_isOffline = true;

    /**
     * Payment Method can be used in checkout backend
     *
     * @var bool
     */
    protected $_canUseInternal = true;

    /**
     * Payment Method cannot be used in checkout frontend
     *
     * @var bool
     */
    protected $_canUseCheckout = false;

    /**
     * @param CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(CartInterface $quote = null)
    {
        return parent::isAvailable($quote);
    }
}
