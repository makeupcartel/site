<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Source\Post\SharingButtons;

/**
 * Display sharing buttons at source model
 * @package Aheadworks\Blog\Model\Source\Poast
 */
class DisplayAt implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * 'Post' option
     */
    const POST = 1;

    /**
     * 'List of Posts' option
     */
    const POST_LIST = 2;

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
                    'value' => self::POST,
                    'label' => __('Post')
                ],
                [
                    'value' => self::POST_LIST,
                    'label' => __('List of Posts')
                ]
            ];
        }
        return $this->options;
    }
}
