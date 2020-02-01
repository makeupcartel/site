<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Source\Config\Cms;

/**
 * Cms Block source model
 * @package Aheadworks\Blog\Model\Source\Config\Cms
 */
class Block implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * 'Don\'t display' option
     */
    const DONT_DISPLAY = -1;

    /**
     * @var \Magento\Cms\Model\ResourceModel\Block\Collection
     */
    private $blockCollection;

    /**
     * @var array
     */
    private $options;

    /**
     * @param \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $blockCollectionFactory
     */
    public function __construct(\Magento\Cms\Model\ResourceModel\Block\CollectionFactory $blockCollectionFactory)
    {
        $this->blockCollection = $blockCollectionFactory->create();
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = array_merge(
                [self::DONT_DISPLAY => __('Don\'t display')],
                $this->blockCollection->toOptionArray()
            );
        }
        return $this->options;
    }
}
