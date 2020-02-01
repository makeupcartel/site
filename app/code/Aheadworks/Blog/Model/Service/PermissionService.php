<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Service;

use Aheadworks\Blog\Api\PermissionManagementInterface;
use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\Model\Post\Permission as PostPermission;

/**
 * Class PermissionService
 * @package Aheadworks\Blog\Model\Service
 */
class PermissionService implements PermissionManagementInterface
{
    /**
     * @var PostPermission
     */
    private $postPermission;

    /**
     * @param PostPermission $postPermission
     */
    public function __construct(
        PostPermission $postPermission
    ) {
        $this->postPermission = $postPermission;
    }

    /**
     * {@inheritdoc}
     */
    public function isPostAllowed(PostInterface $post, $storeId, $customerGroupId)
    {
        return $this->postPermission->isPostAllowed($post, $storeId, $customerGroupId);
    }
}
