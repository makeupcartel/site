<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Controller\Adminhtml\Post;

use Aheadworks\Blog\Api\Data\PostInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\Error;
use Magento\Framework\Message\MessageInterface;

/**
 * Class Validate
 * @package Aheadworks\Blog\Controller\Adminhtml\Post
 */
class Validate extends \Aheadworks\Blog\Controller\Adminhtml\Post
{
    /**
     * Validate post
     *
     * @param array $response
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function validate($response)
    {
        $errors = [];
        if ($postData = $this->getRequest()->getPostValue()) {
            try {
                /** @var PostInterface $postDataObject */
                $postDataObject = $this->postDataFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $postDataObject,
                    $this->preparePostData($postData),
                    PostInterface::class
                );
                /** @var \Aheadworks\Blog\Model\Post $postModel */
                $postModel = $this->postFactory->create();
                $postModel->setData(
                    $this->dataObjectProcessor->buildOutputDataArray(
                        $postDataObject,
                        PostInterface::class
                    )
                );
                $postModel->validateBeforeSave();
            } catch (\Magento\Framework\Validator\Exception $exception) {
                /* @var $error Error */
                foreach ($exception->getMessages(MessageInterface::TYPE_ERROR) as $error) {
                    $errors[] = $error->getText();
                }
            } catch (LocalizedException $exception) {
                $errors[] = $exception->getMessage();
            }
        }
        if ($errors) {
            $messages = $response->hasMessages() ? $response->getMessages() : [];
            foreach ($errors as $error) {
                $messages[] = $error;
            }
            $response->setMessages($messages);
            $response->setError(1);
        }
    }

    /**
     * AJAX post validate action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        $response->setError(0);

        $this->validate($response);
        $resultJson = $this->resultJsonFactory->create();
        if ($response->getError()) {
            $response->setError(true);
            $response->setMessages($response->getMessages());
        }

        $resultJson->setData($response);
        return $resultJson;
    }
}
