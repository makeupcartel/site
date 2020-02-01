<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Url\Canonical;

use Aheadworks\Blog\Api\CategoryRepositoryInterface;
use Aheadworks\Blog\Api\Data\PostInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Aheadworks\Blog\Api\Data\CategoryInterface;
use Aheadworks\Blog\Model\Url\TypeResolver as UrlTypeResolver;

class Category
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var UrlTypeResolver
     */
    private $urlTypeResolver;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param UrlTypeResolver $urlTypeResolver
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        UrlTypeResolver $urlTypeResolver
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->urlTypeResolver = $urlTypeResolver;
    }

    /**
     * Retrieve canonical category or return the first available
     *    category from list of post categories
     *
     * @param PostInterface $post
     * @return CategoryInterface|bool
     */
    public function getCanonicalCategory(PostInterface $post)
    {
        $canonicalCategoryId = $post->getCanonicalCategoryId();
        if ($this->urlTypeResolver->isCategoryIncl() && $canonicalCategoryId && !empty($post->getCategoryIds())) {
            if (in_array($canonicalCategoryId, $post->getCategoryIds())) {
                return $this->categoryRepository->get($canonicalCategoryId);
            }
        }
        if ($this->urlTypeResolver->isCategoryIncl() && !empty($post->getCategoryIds())) {
            $categoryArray = $this->getSortedCategories($post->getCategoryIds());
            return array_shift($categoryArray);
        }
        return false;
    }

    /**
     * Get list of categories sorted by sort_order
     *
     * @param array $categoryIds
     * @return CategoryInterface[]
     */
    private function getSortedCategories(array $categoryIds)
    {
        $sortOrder = $this->sortOrderBuilder
            ->setField(CategoryInterface::SORT_ORDER)
            ->setDirection(SortOrder::SORT_ASC)
            ->create();
        $this->searchCriteriaBuilder
            ->addFilter(CategoryInterface::ID, $categoryIds, 'in')
            ->addSortOrder($sortOrder);
        return $this->categoryRepository->getList($this->searchCriteriaBuilder->create())->getItems();
    }
}
