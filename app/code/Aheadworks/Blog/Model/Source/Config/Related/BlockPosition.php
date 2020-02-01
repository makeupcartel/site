<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Source\Config\Related;

/**
 * Class BlockPosition
 * @package Aheadworks\Blog\Model\Source\Config\Related
 */
class BlockPosition implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var string
     */
    const AFTER_POST = 'after_post';
    const AFTER_COMMENTS = 'after_comments';
    const NOT_DISPLAY = 'not_display';

    /**
     * Retrieve block position types as option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::AFTER_POST, 'label' => __('After post')],
            ['value' => self::AFTER_COMMENTS, 'label' => __('After comments')],
            ['value' => self::NOT_DISPLAY, 'label' => __('Don\'t display')]
        ];
    }
}
