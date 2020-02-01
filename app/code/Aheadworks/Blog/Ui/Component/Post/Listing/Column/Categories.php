<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Ui\Component\Post\Listing\Column;

use Aheadworks\Blog\Api\Data\CategoryInterface;
use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\Api\CategoryRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Categories
 * @package Aheadworks\Blog\Ui\Component\Post\Listing\Column
 */
class Categories extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param CategoryRepositoryInterface $categoryRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CategoryRepositoryInterface $categoryRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $post) {
                if (is_array($post[PostInterface::CATEGORY_IDS])) {
                    $categories = $this->categoryRepository
                        ->getList($this->getSearchCriteria($post[PostInterface::CATEGORY_IDS]))
                        ->getItems();
                    $categoryNames = [];
                    foreach ($categories as $category) {
                        $categoryNames[] = $category->getName();
                    }
                    $post['categories'] = implode(', ', $categoryNames);
                }
            }
        }
        return $dataSource;
    }

    /**
     * @param array $categoryIds
     * @return \Magento\Framework\Api\SearchCriteria
     */
    private function getSearchCriteria(array $categoryIds)
    {
        return $this->searchCriteriaBuilder
            ->addFilter(CategoryInterface::ID, $categoryIds, 'in')
            ->create();
    }
}
