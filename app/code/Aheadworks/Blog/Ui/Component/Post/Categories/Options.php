<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Ui\Component\Post\Categories;

use Aheadworks\Blog\Api\Data\CategoryInterface;
use Aheadworks\Blog\Model\ResourceModel\Category\Collection;
use Magento\Framework\Data\OptionSourceInterface;
use Aheadworks\Blog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

/**
 * Class Options
 * @package Aheadworks\Blog\Ui\Component\Post\Form\Element\Categories
 */
class Options implements OptionSourceInterface
{
    /**
     * Tree index
     */
    const TREE = 'tree';

    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var array
     */
    private $options;

    /**
     * @param CategoryCollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        CategoryCollectionFactory $categoryCollectionFactory
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            /** @var Collection $collection */
            $collection = $this->categoryCollectionFactory->create();
            $collection->setOrder(CategoryInterface::SORT_ORDER, Collection::SORT_ORDER_ASC);
            $tree = [self::TREE => []];

            /** @var CategoryInterface $category */
            foreach ($collection as $category) {
                foreach ([$category->getId(), (int)$category->getParentId()] as $categoryId) {
                    if (!isset($tree[$categoryId])) {
                        $tree[$categoryId] = ['value' => $categoryId];
                    }
                }

                $tree[$category->getId()] = [
                    'label' => $category->getName(),
                    'value' => $category->getId()
                ];
                (int)$category->getParentId()
                    ? $tree[$category->getParentId()]['optgroup'][] = &$tree[$category->getId()]
                    : $tree[self::TREE][] = &$tree[$category->getId()];
            }
            $this->options = $tree[self::TREE];
        }
        return $this->options;
    }
}
