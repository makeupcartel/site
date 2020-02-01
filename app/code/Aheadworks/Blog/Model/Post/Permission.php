<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Post;

use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\Model\Source\Post\Status;
use Aheadworks\Blog\Model\Source\Post\CustomerGroups;

/**
 * Class Permission
 * @package Aheadworks\Blog\Model\Post
 */
class Permission
{
    /**
     * Check if post available to display
     *
     * @param PostInterface $post
     * @param int $storeId
     * @param int $customerGroupId
     * @return bool
     */
    public function isPostAllowed(PostInterface $post, $storeId, $customerGroupId)
    {
        if (!$this->isPostPublished($post)
            || !$this->isPostReleased($post)
            || !$this->isAllowedForStore($post, $storeId)
            || !$this->isAllowedForCustomerGroup($post, $customerGroupId)
        ) {
            return false;
        }
        return true;
    }

    /**
     * Check if post is published
     * @param PostInterface $post
     * @return bool
     */
    private function isPostPublished(PostInterface $post)
    {
        if ($post->getStatus() == Status::PUBLICATION) {
            return true;
        }
        return false;
    }

    /**
     * Check if post is released
     * @param PostInterface $post
     * @return bool
     */
    private function isPostReleased(PostInterface $post)
    {
        $currentDate = new \DateTime();
        $publishDate = new \DateTime($post->getPublishDate());
        if ($publishDate > $currentDate) {
            return false;
        }
        return true;
    }

    /**
     * Check is post is visible for specified store
     *
     * @param PostInterface $post
     * @param int $storeId
     * @return bool
     */
    private function isAllowedForStore(PostInterface $post, $storeId)
    {
        if (!in_array($storeId, $post->getStoreIds())
            && !in_array(0, $post->getStoreIds())) {
            return false;
        }
        return true;
    }

    /**
     * Check if post is visible for specified customer group
     *
     * @param PostInterface $post
     * @param int $customerGroupId
     * @return bool
     */
    private function isAllowedForCustomerGroup(PostInterface $post, $customerGroupId)
    {
        $customerGroupList = explode(',', $post->getCustomerGroups());
        if (is_array($customerGroupList) && in_array(CustomerGroups::ALL_GROUPS, $customerGroupList)
            || (is_array($customerGroupList) && in_array($customerGroupId, $customerGroupList))
        ) {
            return true;
        }
        return false;
    }
}
