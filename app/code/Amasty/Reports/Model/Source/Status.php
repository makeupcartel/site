<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Status
 */
class Status implements ArrayInterface
{
    const PROCESSING = 0;
    const COMPLETE = 1;
    const EMPTY_CART = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::PROCESSING,
                'label' => __('Processing')
            ],
            [
                'value' => self::COMPLETE,
                'label' => __('Complete')
            ],
            [
                'value' => self::EMPTY_CART,
                'label' => __('Empty cart')
            ]
        ];
    }
}
