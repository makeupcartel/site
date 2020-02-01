<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Model\Cart;

class ShippingMethod extends \Magento\Quote\Model\Cart\ShippingMethod implements
    \Amasty\ShippingTableRates\Api\Data\ShippingMethodInterface
{
    public function setComment($comment)
    {
        return $this->setData('comment', $comment);
    }

    public function getComment()
    {
        return $this->_get('comment');
    }
}
