<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Source;

class RelevanceRuleModificationType implements \Magento\Framework\Option\ArrayInterface
{
    const INCREASE = '0';
    const DECREASE = '1';

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::INCREASE, 'label' => __('Increase by')],
            ['value' => self::DECREASE, 'label' => __('Decrease by')]
        ];
    }
}
