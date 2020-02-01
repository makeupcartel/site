<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Source\Category;

/**
 * Category Status source model
 * @package Aheadworks\Blog\Model\Source\Category
 */
class Status implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * 'Enabled' status
     */
    const ENABLED = 1;

    /**
     * 'Disabled' status
     */
    const DISABLED = 0;

    /**
     * @var array
     */
    private $options;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [
                [
                    'value' => self::DISABLED,
                    'label' => __('Disabled')
                ],
                [
                    'value' => self::ENABLED,
                    'label' => __('Enabled')
                ]
            ];
        }
        return $this->options;
    }
}
