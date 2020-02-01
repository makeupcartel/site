<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Controller\Index;

use Aheadworks\Blog\Controller\Action;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Index
 * @package Aheadworks\Blog\Controller\Index
 */
class Index extends Action
{
    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $pageConfig = $resultPage->getConfig();

        if ($tagId = (int)$this->getRequest()->getParam('tag_id')) {
            try {
                $tag = $this->tagRepository->get($tagId);
                $pageConfig->getTitle()->set(__("Tagged with '%1'", $tag->getName()));
            } catch (LocalizedException $e) {
                $pageConfig->getTitle()->set($this->getBlogTitle());
            }
        } else {
            $pageConfig->getTitle()->set($this->getBlogTitle());
        }
        $pageConfig->setMetadata('description', $this->getBlogMetaDescription());

        return $resultPage;
    }
}
