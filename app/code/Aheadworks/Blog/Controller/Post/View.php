<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Controller\Post;

use Aheadworks\Blog\Controller\Action;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class View
 * @package Aheadworks\Blog\Controller\Post
 */
class View extends Action
{
    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        if ($postId = (int)$this->getRequest()->getParam('post_id')) {
            try {
                $post = $this->postRepository->get($postId);
                if (!$this->dataChecker->isPostVisible($post, $this->getStoreId())) {
                    return $this->noRoute();
                }
                $categoryId = $this->getRequest()->getParam('blog_category_id');
                $exclCategoryFromUrl = $this->urlTypeResolver->isCategoryExcl() && $categoryId ? true : false;
                if ($exclCategoryFromUrl || $categoryId && !in_array($categoryId, $post->getCategoryIds())) {
                    // Forced redirect to post url without category id
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setUrl($this->url->getPostUrl($post));
                    return $resultRedirect;
                }

                $title = $post->getMetaTitle() ? $post->getMetaTitle() : $post->getTitle();
                $resultPage = $this->resultPageFactory->create();
                $pageConfig = $resultPage->getConfig();
                $pageConfig->getTitle()->set($title);
                $pageConfig->setMetadata('description', $post->getMetaDescription());
                $pageConfig->addRemotePageAsset(
                    $this->url->getCanonicalUrl($post),
                    'canonical',
                    ['attributes' => ['rel' => 'canonical']]
                );
                return $resultPage;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $this->goBack();
            }
        }

        return $this->noRoute();
    }
}
