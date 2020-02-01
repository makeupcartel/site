<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Convert\Wishlist\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Wishlist\Model\Item\Option;
use Magento\Wishlist\Model\Item\OptionFactory;
use Magento\Wishlist\Model\ResourceModel\Item\Option\CollectionFactory;
use Magento\Catalog\Model\Product\Exception as ProductException;

class Item extends \Magento\Wishlist\Model\Item implements ItemInterface
{

	// don't delete product in wishlist when add to cart in wishlist
    public function addToCart(\Magento\Checkout\Model\Cart $cart, $delete = false)
    {
        $product = $this->getProduct();

        $storeId = $this->getStoreId();

        if ($product->getStatus() != \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) {
            return false;
        }

        if (!$product->isVisibleInSiteVisibility()) {
            if ($product->getStoreId() == $storeId) {
                return false;
            }
            $urlData = $this->_catalogUrl->getRewriteByProductStore([$product->getId() => $storeId]);
            if (!isset($urlData[$product->getId()])) {
                return false;
            }
            $product->setUrlDataObject(new \Magento\Framework\DataObject($urlData));
            $visibility = $product->getUrlDataObject()->getVisibility();
            if (!in_array($visibility, $product->getVisibleInSiteVisibilities())) {
                return false;
            }
        }

        if (!$product->isSalable()) {
            throw new ProductException(__('Product is not salable.'));
        }

        $buyRequest = $this->getBuyRequest();

        $cart->addProduct($product, $buyRequest);
        if (!$product->isVisibleInSiteVisibility()) {
            $cart->getQuote()->getItemByProduct($product)->setStoreId($storeId);
        }

        /*if ($delete) {
            $this->delete();
        }*/

        return true;
    }

}
