<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */

namespace Amasty\Promo\Model\Config\Source\Banner;

/**
 * Class Mode.php
 *
 * @author Artem Brunevski
 */

class Mode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray($withAdmin = false)
    {
        $optionArray = [];
        $arr         = $this->toArray($withAdmin);
        foreach ($arr as $value => $label) {
            $optionArray[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            \Amasty\Promo\Block\Banner::MODE_CART    => __('Cart contents'),
            \Amasty\Promo\Block\Banner::MODE_PRODUCT => __('Product page')
        ];
    }
}