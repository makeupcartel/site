<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Source\Post;

/**
 * Post Status source model
 * @package Aheadworks\Blog\Model\Source\Post
 */
class Status implements \Magento\Framework\Option\ArrayInterface
{
    // Statuses to store in DB
    const DRAFT = 'draft';
    const PUBLICATION = 'publication';
    const SCHEDULED = 'scheduled';

    /**
     * @var array
     */
    private $options;

    /**
     * @return array
     */
    public function getOptions()
    {
        return [
            self::DRAFT => __('Draft'),
            self::SCHEDULED => __('Scheduled'),
            self::PUBLICATION => __('Published')
        ];
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = [];
            foreach ($this->getOptions() as $value => $label) {
                $this->options[] = ['value' => $value, 'label' => $label];
            }
        }
        return $this->options;
    }
}
