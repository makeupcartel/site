<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Controller\Adminhtml\Comment;

use Aheadworks\Blog\Model\DisqusCommentsService;
use Magento\Backend\App\Action\Context;

/**
 * Class Index
 * @package Aheadworks\Blog\Controller\Adminhtml\Comment
 */
class Index extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Aheadworks_Blog::comments';

    /**
     * @var DisqusCommentsService
     */
    private $disqusCommentsService;

    /**
     * @param Context $context
     * @param DisqusCommentsService $disqusCommentsService
     */
    public function __construct(
        Context $context,
        DisqusCommentsService $disqusCommentsService
    ) {
        parent::__construct($context);
        $this->disqusCommentsService = $disqusCommentsService;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($this->disqusCommentsService->getModerateUrl());
        return $resultRedirect;
    }
}
