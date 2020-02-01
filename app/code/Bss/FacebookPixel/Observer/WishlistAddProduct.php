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

class WishlistAddProduct implements ObserverInterface
{

    /**
     * @var \Bss\FacebookPixel\Model\Session
     */
    protected $fbPixelSession;

    /**
     * @var \Bss\FacebookPixel\Helper\Data
     */
    protected $helper;

    /**
     * WishlistAddProduct constructor.
     * @param \Bss\FacebookPixel\Model\Session $fbPixelSession
     * @param \Bss\FacebookPixel\Helper\Data $helper
     */
    public function __construct(
        \Bss\FacebookPixel\Model\Session $fbPixelSession,
        \Bss\FacebookPixel\Helper\Data $helper
    ) {
        $this->fbPixelSession = $fbPixelSession;
        $this->helper        = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return boolean
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getItem()->getProduct();
        /** @var \Magento\Catalog\Model\Product $product */
        if (!$this->helper->getConfig('bss_facebook_pixel/event_tracking/add_to_wishlist') || !$product) {
            return true;
        }
        
        $data = [
            'content_type' => 'product',
            'content_ids' => $product->getSku(),
            'content_name' => $product->getName(),
            'currency' => $this->helper->getCurrencyCode()
        ];

        $this->fbPixelSession->setAddToWishlist($data);
        
        return true;
    }
}
