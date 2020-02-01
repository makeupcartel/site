<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Oaction
 */
namespace Amasty\Oaction\Model\Source;

class Status extends  \Magento\Sales\Model\Config\Source\Order\Status
{
    public function toOptionArray()
    {
        $options = parent::toOptionArray();
        $options[0] = array(
            'value' => '',
            'label' => __('Magento Default')
        );
        return $options;
    }
}