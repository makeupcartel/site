<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Controller\Author;

use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Blog\Controller\Action;

/**
 * Class View
 * @package Aheadworks\Blog\Controller\Author
 */
class View extends Action
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($authorId = (int)$this->getRequest()->getParam('author_id')) {
            try {
                $author = $this->authorRepository->get($authorId);
                $resultPage = $this->resultPageFactory->create();
                $pageTitle = $author->getFirstname() . ' ' . $author->getLastname();
                $pageConfig = $resultPage->getConfig();
                $pageConfig->getTitle()->set($pageTitle);
                $pageConfig->setMetadata('description', $this->config->getBlogMetaDescription());
                return $resultPage;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $this->goBack();
            }
        }

        return $this->noRoute();
    }
}
