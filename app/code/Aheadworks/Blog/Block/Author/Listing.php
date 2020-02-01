<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Block\Author;

use Aheadworks\Blog\Api\Data\AuthorInterface;
use Aheadworks\Blog\Api\AuthorRepositoryInterface;
use Aheadworks\Blog\Block\Html\Pager;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Listing
 * @package Aheadworks\Blog\Block\Author
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Listing
{
    /**
     * @var AuthorRepositoryInterface
     */
    private $authorRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @param AuthorRepositoryInterface $authorRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        AuthorRepositoryInterface $authorRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->authorRepository = $authorRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     * Get authors list
     *
     * @return \Aheadworks\Blog\Api\Data\AuthorInterface[]
     * @throws LocalizedException
     */
    public function getAuthors()
    {
        return $this->authorRepository
            ->getList($this->buildSearchCriteria())
            ->getItems();
    }

    /**
     * Retrieves search criteria builder
     *
     * @return SearchCriteriaBuilder
     */
    public function getSearchCriteriaBuilder()
    {
        return $this->searchCriteriaBuilder;
    }

    /**
     * Apply pagination
     *
     * @param Pager $pager
     * @return void
     */
    public function applyPagination(Pager $pager)
    {
        $this->prepareSearchCriteriaBuilder();
        $pager->applyPagination($this->searchCriteriaBuilder);
    }

    /**
     * Build search criteria
     *
     * @return \Magento\Framework\Api\SearchCriteria
     */
    private function buildSearchCriteria()
    {
        $this->prepareSearchCriteriaBuilder();
        return $this->searchCriteriaBuilder->create();
    }

    /**
     * Prepares search criteria builder
     *
     * @return void
     */
    private function prepareSearchCriteriaBuilder()
    {
        /** @var \Magento\Framework\Api\SortOrder $nameOrder */
        $nameOrder = $this->sortOrderBuilder
            ->setField(AuthorInterface::ID)
            ->setDescendingDirection()
            ->create();
        $this->searchCriteriaBuilder->addSortOrder($nameOrder);
    }
}
