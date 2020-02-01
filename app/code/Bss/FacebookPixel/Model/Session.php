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
namespace Bss\FacebookPixel\Model;

class Session extends \Magento\Framework\Session\SessionManager
{
    /**
     * @param array $data
     * @return \Bss\FacebookPixel\Model\Session $this
     */
    public function setAddToCart($data)
    {
        $this->setData('add_to_cart', $data);
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getAddToCart()
    {
        if ($this->hasAddToCart()) {
            $data = $this->getData('add_to_cart');
            $this->unsetData('add_to_cart');
            return $data;
        }
        return null;
    }

    /**
     * @return bool
     */
    public function hasAddToCart()
    {
        return $this->hasData('add_to_cart');
    }

    /**
     * @param array $data
     * @return \Bss\FacebookPixel\Model\Session $this
     */
    public function setAddToWishlist($data)
    {
        $this->setData('add_to_wishlist', $data);
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getAddToWishlist()
    {
        if ($this->hasAddToWishlist()) {
            $data = $this->getData('add_to_wishlist');
            $this->unsetData('add_to_wishlist');
            return $data;
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function hasAddToWishlist()
    {
        return $this->hasData('add_to_wishlist');
    }

    /**
     * @param array $data
     * @return \Bss\FacebookPixel\Model\Session $this
     */
    public function setAddSubscribe($data)
    {
        $this->setData('add_subscribe', $data);
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getAddSubscribe()
    {
        if ($this->hasAddSubscribe()) {
            $data = $this->getData('add_subscribe');
            $this->unsetData('add_subscribe');
            return $data;
        }
        return null;
    }

    /**
     * @return bool
     */
    public function hasAddSubscribe()
    {
        return $this->hasData('add_subscribe');
    }

    /**
     * @return bool
     */
    public function hasInitiateCheckout()
    {
        return $this->hasData('initiate_checkout');
    }

    /**
     * @param array $data
     * @return \Bss\FacebookPixel\Model\Session $this
     */
    public function setInitiateCheckout($data)
    {
        $this->setData('initiate_checkout', $data);
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getInitiateCheckout()
    {
        if ($this->hasInitiateCheckout()) {
            $data = $this->getData('initiate_checkout');
            $this->unsetData('initiate_checkout');
            return $data;
        }
        return null;
    }

    /**
     * @return bool
     */
    public function hasSearch()
    {
        return $this->hasData('search');
    }

    /**
     * @param array $data
     * @return \Bss\FacebookPixel\Model\Session $this
     */
    public function setSearch($data)
    {
        $this->setData('search', $data);
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getSearch()
    {
        if ($this->hasSearch()) {
            $data = $this->getData('search');
            $this->unsetData('search');
            return $data;
        }
        return null;
    }

    /**
     * @return bool
     */
    public function hasRegister()
    {
        return $this->hasData('customer_register');
    }

    /**
     * @param array $data
     * @return \Bss\FacebookPixel\Model\Session $this
     */
    public function setRegister($data)
    {
        $this->setData('customer_register', $data);
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getRegister()
    {
        if ($this->hasRegister()) {
            $data = $this->getData('customer_register');
            $this->unsetData('customer_register');
            return $data;
        }
        return null;
    }

    /**
     * @param array $data
     * @return \Bss\FacebookPixel\Model\Session $this
     */
    public function setActionPage($data)
    {
        $this->setData('bss_action_page', $data);
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getActionPage()
    {
        if ($this->hasActionPage()) {
            $data = $this->getData('bss_action_page');
            $this->unsetData('bss_action_page');
            return $data;
        }
        return null;
    }

    /**
     * @return bool
     */
    public function hasActionPage()
    {
        return $this->hasData('bss_action_page');
    }
}
