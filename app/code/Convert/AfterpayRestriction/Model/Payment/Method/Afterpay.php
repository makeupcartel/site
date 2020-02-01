<?php

namespace Convert\AfterpayRestriction\Model\Payment\Method;

/**
 * Class Afterpay
 *
 * @package Convert\AfterpayRestriction\Model\Payment\Method
 */
class Afterpay extends \Afterpay\Afterpay\Model\Payovertime
{
    const GIFT_CARD = 'giftcard';

    /**
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if ($quote) {
            $isAfterpay = false;
            $items = $quote->getAllItems();
            /** @var \Magento\Quote\Model\Quote\Item $item */
            foreach($items as $item) {
                $product = $item->getProduct();
                if (self::GIFT_CARD === $product->getTypeId()
                    || $product->getResource()->getAttributeRawValue($product->getId(),'available_afterpay',$item->getStoreId())) {
                    $isAfterpay = true;
                    break;
                }
            }
            if($isAfterpay){
                return;
            }
        }
        return parent::isAvailable($quote);
    }
}
