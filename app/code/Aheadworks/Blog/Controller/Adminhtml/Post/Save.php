<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Controller\Adminhtml\Post;

use Aheadworks\Blog\Api\Data\PostInterface;
use Magento\Framework\Message\Error;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Save
 * @package Aheadworks\Blog\Controller\Adminhtml\Post
 */
class Save extends \Aheadworks\Blog\Controller\Adminhtml\Post
{
    /**
     * Save post action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($postData = $this->getRequest()->getPostValue()) {
            $postData = $this->preparePostData($postData);
            $postId = isset($postData['id']) ? $postData['id'] : false;
            try {
                $postDataObject = $postId
                    ? $this->postRepository->get($postId)
                    : $this->postDataFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $postDataObject,
                    $postData,
                    PostInterface::class
                );
                $post = $this->postRepository->save($postDataObject);
                $this->dataPersistor->clear('aw_blog_post');
                $this->messageManager->addSuccessMessage(__('The post was successfully saved.'));
                $back = $this->getRequest()->getParam('back');
                if ($back == 'edit') {
                    return $resultRedirect->setPath(
                        '*/*/' . $back,
                        [
                            'id' => $post->getId(),
                            '_current' => true
                        ]
                    );
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Validator\Exception $exception) {
                $messages = $exception->getMessages();
                if (empty($messages)) {
                    $messages = [$exception->getMessage()];
                }
                foreach ($messages as $message) {
                    if (!$message instanceof Error) {
                        $message = new Error($message);
                    }
                    $this->messageManager->addMessage($message);
                }
            } catch (LocalizedException $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('Something went wrong while saving the post.')
                );
            }
            $this->dataPersistor->set('aw_blog_post', $postData);
            if ($postId) {
                return $resultRedirect->setPath('*/*/edit', ['id' => $postId, '_current' => true]);
            }
            return $resultRedirect->setPath('*/*/new', ['_current' => true]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
