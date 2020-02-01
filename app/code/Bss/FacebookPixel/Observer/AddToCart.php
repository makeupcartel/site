<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_FacebookPixel
 * @author    Extension Team
 * @copyright Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\FacebookPixel\Observer;

use Magento\Framework\Event\ObserverInterface;

class AddToCart implements ObserverInterface
{
    /**
     * @var \Bss\FacebookPixel\Model\Session
     */
    protected $fbPixelSession;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    /**
     * @var \Bss\FacebookPixel\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $dataPrice;

    /**
     * AddToCart constructor.
     * @param \Bss\FacebookPixel\Model\Session $fbPixelSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Bss\FacebookPixel\Helper\Data $helper
     */
    public function __construct(
        \Bss\FacebookPixel\Model\Session $fbPixelSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Bss\FacebookPixel\Helper\Data $helper
    ) {
        $this->fbPixelSession = $fbPixelSession;
        $this->checkoutSession = $checkoutSession;
        $this->helper        = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $items = $observer->getItems();
        $typeConfi = \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE;
        if (!$this->helper->getConfig('bss_facebook_pixel/event_tracking/add_to_cart') || !$items) {
            return true;
        }
        $product = [
            'content_ids' => [],
            'currency' => ""
        ];

        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($items as $item) {
            if ($item->getProduct()->getTypeId() == $typeConfi) {
                continue;
            }
            if ($item->getParentItem()) {
                if ($item->getParentItem()->getProductType() == $typeConfi) {
                    $product['contents'][] = [
                        'id' => $item->getSku(),
                        'name' => $item->getName(),
                        'quantity' => $item->getParentItem()->getQtyToAdd()
                    ];
                } else {
                    $product['contents'][] = [
                        'id' => $item->getSku(),
                        'name' => $item->getName(),
                        'quantity' => $item->getData('qty')
                    ];
                }
            } else {
                $product['contents'][] = [
                    'id' => $item->getSku(),
                    'name' => $item->getName(),
                    'quantity' => $item->getQtyToAdd()
                ];
            }
            $product['content_ids'][] = $item->getSku();
        }

        $data = [
            'content_type' => 'product',
            'content_ids' => $product['content_ids'],
            'contents' => $product['contents'],
            'currency' => $this->helper->getCurrencyCode(),
        ];

        $this->fbPixelSession->setAddToCart($data);

        return true;
    }
}
