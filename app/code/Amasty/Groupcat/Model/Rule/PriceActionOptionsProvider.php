<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

namespace Amasty\Groupcat\Model\Rule;

class PriceActionOptionsProvider implements \Magento\Framework\Data\OptionSourceInterface
{
    const SHOW = 0;
    const HIDE = 1;
    const REPLACE = 2;
    const REPLACE_REQUEST = 3;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::SHOW, 'label' => __('No')],
            ['value' => self::HIDE, 'label' => __('Yes')],
            ['value' => self::REPLACE, 'label' => __('Replace with text')],
            ['value' => self::REPLACE_REQUEST, 'label' => __('Replace to request form')]
        ];
    }
}
