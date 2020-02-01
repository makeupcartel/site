<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Model\Source;

class RedirectType implements \Magento\Framework\Option\ArrayInterface
{
    const AJAX_REFRESH = 0;
    const REDIRECT_TO_CUSTOM_URL = 1;
    const REFRESH_PAGE = 2;

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
            self::AJAX_REFRESH => __('Stay on the current page'),
            self::REDIRECT_TO_CUSTOM_URL => __('To Custom Url'),
            self::REFRESH_PAGE => __('Refresh Current Page')
        ];
    }
}
