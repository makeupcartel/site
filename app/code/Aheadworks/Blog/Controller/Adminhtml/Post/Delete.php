<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Controller\Adminhtml\Post;

/**
 * Class Delete
 * @package Aheadworks\Blog\Controller\Adminhtml\Post
 */
class Delete extends \Aheadworks\Blog\Controller\Adminhtml\Post
{
    /**
     * Delete post action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $postId = (int)$this->getRequest()->getParam('id');
        if ($postId) {
            try {
                $this->postRepository->deleteById($postId);
                $this->messageManager->addSuccessMessage(__('Post was successfully deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            }
        }
        $this->messageManager->addErrorMessage(__('Post could not be deleted.'));
        return $resultRedirect->setPath('*/*/');
    }
}
