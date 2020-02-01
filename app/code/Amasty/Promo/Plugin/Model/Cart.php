<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Plugin\Model;

class Cart
{
    /**
     * @var \Amasty\Promo\Helper\Item
     */
    private $helperItem;

    public function __construct(\Amasty\Promo\Helper\Item $helperItem)
    {
        $this->helperItem = $helperItem;
    }

    /**
     * @param \Magento\Checkout\Model\Cart $cart
     * @param $data
     * @return mixed
     */
    public function beforeUpdateItems(\Magento\Checkout\Model\Cart $cart, $data)
    {
        foreach ($data as $itemId => $itemInfo) {
            $item = $cart->getQuote()->getItemById($itemId);
            if ($item && $this->helperItem->isPromoItem($item)) {
                $data[$itemId]['qty'] = $item->getQty();
                $data[$itemId]['before_suggest_qty'] = $item->getQty();
            }
        }

        return [$data];
    }
}
