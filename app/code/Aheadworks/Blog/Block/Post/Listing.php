<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Block\Post;

use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\Api\PostRepositoryInterface;
use Aheadworks\Blog\Api\TagRepositoryInterface;
use Aheadworks\Blog\Block\Html\Pager;
use Aheadworks\Blog\Model\Source\Post\Status;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Customer\Model\Context as CustomerContext;

/**
 * Class Listing
 * @package Aheadworks\Blog\Block\Post
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Listing
{
    /**
     * @var PostRepositoryInterface
     */
    private $postRepository;

    /**
     * @var TagRepositoryInterface
     */
    private $tagRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @param PostRepositoryInterface $postRepository
     * @param TagRepositoryInterface $tagRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface $request
     * @param DateTime $dateTime
     * @param HttpContext $httpContext
     */
    public function __construct(
        PostRepositoryInterface $postRepository,
        TagRepositoryInterface $tagRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        DateTime $dateTime,
        HttpContext $httpContext
    ) {
        $this->postRepository = $postRepository;
        $this->tagRepository = $tagRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->dateTime = $dateTime;
        $this->httpContext = $httpContext;
    }

    /**
     * Get posts list
     *
     * @return \Aheadworks\Blog\Api\Data\PostInterface[]
     */
    public function getPosts()
    {
        return $this->postRepository
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
        $this->searchCriteriaBuilder
            ->addFilter(PostInterface::STATUS, Status::PUBLICATION)
            ->addFilter(PostInterface::STORE_IDS, $this->storeManager->getStore()->getId())
            ->addFilter(PostInterface::CUSTOMER_GROUPS, $this->httpContext->getValue(CustomerContext::CONTEXT_GROUP));
        /** @var \Magento\Framework\Api\SortOrder $publishDateOrder */
        $publishDateOrder = $this->sortOrderBuilder
            ->setField(PostInterface::PUBLISH_DATE)
            ->setDescendingDirection()
            ->create();
        $this->searchCriteriaBuilder->addSortOrder($publishDateOrder);
        if ($this->request->getParam('blog_category_id')) {
            $this->searchCriteriaBuilder->addFilter(
                PostInterface::CATEGORY_IDS,
                $this->request->getParam('blog_category_id')
            );
        }
        if ($tagId = $this->request->getParam('tag_id')) {
            $this->searchCriteriaBuilder->addFilter('tag_id', $tagId);
        }
        if ($authorId = $this->request->getParam('author_id')) {
            $this->searchCriteriaBuilder->addFilter('author_id', $authorId);
        }
    }
}
