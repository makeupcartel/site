<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Model\Source;

class ButtonPosition implements \Magento\Framework\Option\ArrayInterface
{
    const TOP = 'top';
    
    const BOTTOM = 'bottom';
    
    const RIGHT = 'right';
    
    const LEFT = 'left';

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
            self::TOP => __('Top'),
            self::BOTTOM => __('Bottom'),
            self::RIGHT => __('Right'),
            self::LEFT => __('Left')
        ];
    }
}
