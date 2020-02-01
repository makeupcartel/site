<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Post CRUD interface
 * @api
 */
interface PostRepositoryInterface
{
    /**
     * Save post
     *
     * @param \Aheadworks\Blog\Api\Data\PostInterface $post
     * @return \Aheadworks\Blog\Api\Data\PostInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Aheadworks\Blog\Api\Data\PostInterface $post);

    /**
     * Retrieve post
     *
     * @param int $postId
     * @return \Aheadworks\Blog\Api\Data\PostInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($postId);

    /**
     * Retrieve post by url key
     *
     * @param string $postUrlKey
     * @return \Aheadworks\Blog\Api\Data\PostInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByUrlKey($postUrlKey);

    /**
     * Retrieve post with related posts
     *
     * @param int $postId
     * @param int $storeId
     * @param int $customerGroupId
     * @return \Aheadworks\Blog\Api\Data\PostInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getWithRelatedPosts($postId, $storeId, $customerGroupId);

    /**
     * Retrieve posts matching the specified criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Aheadworks\Blog\Api\Data\PostSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete post
     *
     * @param \Aheadworks\Blog\Api\Data\PostInterface $post
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Aheadworks\Blog\Api\Data\PostInterface $post);

    /**
     * Delete post by ID
     *
     * @param int $postId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($postId);
}
