<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Controller\Adminhtml\Post;

use Magento\Framework\Exception\NoSuchEntityException;
use Aheadworks\Blog\Api\PostRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Edit
 * @package Aheadworks\Blog\Controller\Adminhtml\Post
 */
class Edit extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Aheadworks_Blog::posts';

    /**
     * @var PostRepositoryInterface
     */
    private $postRepository;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @param Context $context
     * @param PostRepositoryInterface $postRepository
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PostRepositoryInterface $postRepository,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->postRepository = $postRepository;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Post edit action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $postId = (int)$this->getRequest()->getParam('id');
        if ($postId) {
            try {
                $this->postRepository->get($postId);
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addException($exception, __('Something went wrong while editing the post.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/');
                return $resultRedirect;
            }
        }
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage
            ->setActiveMenu('Aheadworks_Blog::posts')
            ->getConfig()->getTitle()->prepend(
                $postId ? __('Edit Post') : __('New Post')
            );
        return $resultPage;
    }
}
