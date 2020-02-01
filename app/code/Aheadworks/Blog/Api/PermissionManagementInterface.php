<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Api;

/**
 * Permission management service interface
 * @api
 */
interface PermissionManagementInterface
{
    /**
     * Check if post is allowed
     *
     * @param \Aheadworks\Blog\Api\Data\PostInterface $post
     * @param int $storeId
     * @param int $customerGroupId
     * @return bool
     */
    public function isPostAllowed(\Aheadworks\Blog\Api\Data\PostInterface $post, $storeId, $customerGroupId);
}
