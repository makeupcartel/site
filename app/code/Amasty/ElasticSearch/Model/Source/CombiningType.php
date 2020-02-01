<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Source;

class CombiningType implements \Magento\Framework\Option\ArrayInterface
{
    const ANY = '0';
    const ALL = '1';

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [['value' => self::ANY, 'label' => __('OR')], ['value' => self::ALL, 'label' => __('AND')]];
    }
}
