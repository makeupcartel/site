<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */

namespace Amasty\Cart\Model\Source;

class BlockType implements \Magento\Framework\Option\ArrayInterface
{
    const RELATED = 'related';
    const CROSSSELL = 'crosssell';
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => '0',
                'label' =>__('None')
            ],
            [
                'value' => self::RELATED,
                'label' =>__('Related')
            ],
            [
                'value' => self::CROSSSELL,
                'label' =>__('Cross-sell')
            ],
        ];
        return $options;
    }
}
