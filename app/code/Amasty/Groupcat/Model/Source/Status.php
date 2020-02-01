<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Model\Source;

class Status implements \Magento\Framework\Option\ArrayInterface
{
    const PENDING = 0;
    const VIEWED = 1;
    const ANSWERED = 2;

    protected $_options;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = [
                [
                    'value' => self::PENDING,
                    'label' => __('Pending')
                ],
                [
                    'value' => self::VIEWED,
                    'label' => __('Viewed')
                ],
                [
                    'value' => self::ANSWERED,
                    'label' => __('Answered')
                ]
            ];
        }

        return $this->_options;
    }

    public function getOptionByValue($value)
    {
        $options = $this->toOptionArray();
        foreach ($options as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }

        return '';
    }
}
