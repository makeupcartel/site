<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Controller;

use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Customer\Model\Context as CustomerContext;
use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\Api\PermissionManagementInterface;

/**
 * Check if post can be displayed
 * @package Aheadworks\Blog\Controller\Post
 */
class Checker
{
    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var PermissionManagementInterface
     */
    private $permissionManagement;

    /**
     * Checker constructor.
     * @param HttpContext $httpContext
     * @param PermissionManagementInterface $permissionManagement
     */
    public function __construct(
        HttpContext $httpContext,
        PermissionManagementInterface $permissionManagement
    ) {
        $this->httpContext = $httpContext;
        $this->permissionManagement = $permissionManagement;
    }

    /**
     * Check if post is visible for current customer group
     * @param PostInterface $post
     * @param $storeId
     * @return bool
     */
    public function isPostVisible(PostInterface $post, $storeId)
    {
        $customerGroupId = $this->httpContext->getValue(CustomerContext::CONTEXT_GROUP);
        return  $this->permissionManagement->isPostAllowed($post, $storeId, $customerGroupId);
    }
}
