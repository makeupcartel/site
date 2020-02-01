<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Source;

class WildcardMode implements \Magento\Framework\Option\ArrayInterface
{
    const BOTH = '1';
    const SUFFIX = '2';
    const PREFIX = '3';

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::BOTH,
                'label' => __('*word*'),
            ],
            [
                'value' => self::SUFFIX,
                'label' => __('word*'),
            ],
            [
                'value' => self::PREFIX,
                'label' => __('*word'),
            ]
        ];
    }
}
