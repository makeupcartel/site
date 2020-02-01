<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Rss\Post;

use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\Api\PostRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Blog\Model\Source\Post\Status;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Api\SortOrder;

/**
 * Class PostListing
 *
 * @package Aheadworks\Blog\Model\Rss\Post
 */
class PostListing
{
    /**
     * @var PostRepositoryInterface
     */
    private $postRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @param PostRepositoryInterface $postRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param DateTime $dateTime
     */
    public function __construct(
        PostRepositoryInterface $postRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        DateTime $dateTime
    ) {
        $this->postRepository = $postRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->dateTime = $dateTime;
    }

    /**
     * Get list of blog posts
     *
     * @param int $storeId
     * @param int $customerGroupId
     * @return PostInterface[]
     * @throws LocalizedException
     */
    public function retrieveListOfPosts($storeId, $customerGroupId)
    {
        $this->searchCriteriaBuilder
            ->addFilter(PostInterface::STATUS, Status::PUBLICATION)
            ->addFilter(PostInterface::STORE_IDS, $storeId)
            ->addFilter(PostInterface::CUSTOMER_GROUPS, $customerGroupId);
        /** @var SortOrder $publishDateOrder */
        $publishDateOrder = $this->sortOrderBuilder
            ->setField(PostInterface::PUBLISH_DATE)
            ->setDescendingDirection()
            ->create();
        $this->searchCriteriaBuilder->setPageSize(10);
        $this->searchCriteriaBuilder->addSortOrder($publishDateOrder);

        return $this->postRepository
            ->getList($this->searchCriteriaBuilder->create())
            ->getItems();
    }
}
