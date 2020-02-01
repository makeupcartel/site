<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Model\Source;

class LoginPosition implements \Magento\Framework\Option\ArrayInterface
{
    const POPUP = 'popup';
    const ABOVE_LOGIN = 'above_login';
    const BELOW_LOGIN = 'below_login';
    const ABOVE_REGISTRATION = 'above_registration';
    const BELOW_REGISTRATION = 'below_registration';
    const CHECKOUT_CART = 'checkout_cart';

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->toArray() as $optionValue => $optionLabel) {
            $options[] = ['value' => $optionValue, 'label' => $optionLabel];
        }
        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::POPUP => __('Login Popup'),
            self::ABOVE_LOGIN => __('Above customer login form'),
            self::BELOW_LOGIN => __('Below customer login form'),
            self::ABOVE_REGISTRATION => __('Above customer registration form'),
            self::BELOW_REGISTRATION => __('Below customer registration form'),
            self::CHECKOUT_CART => __('Shopping cart page'),
        ];
    }
}
