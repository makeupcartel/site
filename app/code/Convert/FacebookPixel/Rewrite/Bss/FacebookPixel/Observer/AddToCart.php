<?php

namespace Convert\FacebookPixel\Rewrite\Bss\FacebookPixel\Observer;

use Bss\FacebookPixel\Observer\AddToCart as BssAddToCart;
use Magento\Framework\Event\Observer;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Class AddToCart
 *
 * @package Convert\FacebookPixel\Rewrite\Bss\FacebookPixel\Observer
 */
class AddToCart extends BssAddToCart
{
    /**
     * @param Observer $observer
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $items = $observer->getItems();
        if (!$this->helper->getConfig('bss_facebook_pixel/event_tracking/add_to_cart') || !$items) {
            return true;
        }
        $product = [
            'content_ids' => [],
            'currency' => '',
            'value' => ''
        ];
        $total = 0.00;
        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($items as $item) {
            if ($item->getProduct()->getTypeId() == Configurable::TYPE_CODE) {
                continue;
            }
            if ($item->getParentItem()) {
                if ($item->getParentItem()->getProductType() == Configurable::TYPE_CODE) {
                    $quantity = $item->getParentItem()->getQtyToAdd();
                    $price = $item->getParentItem()->getPriceInclTax();
                } else {
                    $quantity = $item->getData('qty');
                    $price = $item->getPriceInclTax();
                }
            } else {
                $quantity = $item->getQtyToAdd();
                $price = $item->getPriceInclTax();
            }

            $total += (float) $quantity*$price;
            $product['contents'][] = [
                'id' => $item->getSku(),
                'name' => $item->getName(),
                'quantity' => $quantity,
                'value' => (float) $quantity*$price
            ];
            $product['content_ids'][] = $item->getSku();
        }
        $product['value'] = $total;
        $data = [
            'content_type' => 'product',
            'content_ids' => $product['content_ids'],
            'contents' => $product['contents'],
            'currency' => $this->helper->getCurrencyCode(),
            'value' => ($product['value']>0) ? $product['value'] : 1 // value has to be a decimal number greater than zero
        ];

        $this->fbPixelSession->setAddToCart($data);

        return true;
    }
}
