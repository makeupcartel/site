<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Api;

/**
 * Comments service interface
 * @api
 */
interface CommentsServiceInterface
{
    /**
     * Retrieve total published comments count for a post
     *
     * @param int $postId
     * @param int $storeId
     * @return int
     */
    public function getPublishedCommNum($postId, $storeId);

    /**
     * Retrieve published comments count for posts
     *
     * @param int[] $postIds
     * @param int $storeId
     * @return int[]
     */
    public function getPublishedCommNumBundle($postIds, $storeId);

    /**
     * Retrieve new comments count for posts
     *
     * @param int[] $postIds
     * @param int $storeId
     * @return int[]
     */
    public function getNewCommNumBundle($postIds, $storeId);

    /**
     * Retrieve moderate comments url
     *
     * @param int|null $websiteId
     * @return string
     */
    public function getModerateUrl($websiteId = null);
}
