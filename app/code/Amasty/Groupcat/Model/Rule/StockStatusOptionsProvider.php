<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

namespace Amasty\Groupcat\Model\Rule;

class StockStatusOptionsProvider implements \Magento\Framework\Data\OptionSourceInterface
{
    const ANY_STOCK = 0;
    const OUT_OF_STOCK = 1;
    const IN_STOCK = 2;

    /**
     * @var array|null
     */
    protected $options;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::ANY_STOCK, 'label' => __('Does not matter')],
            ['value' => self::OUT_OF_STOCK, 'label' => __('Out of Stock')],
            ['value' => self::IN_STOCK, 'label' => __('In Stock')]
        ];
    }
}
