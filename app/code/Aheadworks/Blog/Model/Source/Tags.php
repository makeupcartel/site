<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Aheadworks\Blog\Model\ResourceModel\Tag\CollectionFactory as TagCollectionFactory;

/**
 * Class Tags
 * @package Aheadworks\Blog\Model\Source
 */
class Tags implements OptionSourceInterface
{
    /**
     * @var \Aheadworks\Blog\Model\ResourceModel\Tag\Collection
     */
    private $tagCollection;

    /**
     * @var array
     */
    private $options;

    /**
     * @param TagCollectionFactory $tagCollectionFactory
     */
    public function __construct(TagCollectionFactory $tagCollectionFactory)
    {
        $this->tagCollection = $tagCollectionFactory->create();
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = $this->tagCollection->toOptionArray();
        }
        return $this->options;
    }
}
