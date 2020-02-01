<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Aheadworks\Blog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

/**
 * Class Categories
 * @package Aheadworks\Blog\Model\Source
 */
class Categories implements OptionSourceInterface
{
    /**
     * @var \Aheadworks\Blog\Model\ResourceModel\Category\Collection
     */
    private $categoryCollection;

    /**
     * @var array
     */
    private $options;

    /**
     * @param CategoryCollectionFactory $categoryCollectionFactory
     */
    public function __construct(CategoryCollectionFactory $categoryCollectionFactory)
    {
        $this->categoryCollection = $categoryCollectionFactory->create();
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->categoryCollection->setOrder('sort_order', 'ASC');
            $this->options = $this->categoryCollection->toOptionArray();
        }
        return $this->options;
    }
}
