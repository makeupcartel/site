<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Controller\Category;

use Aheadworks\Blog\Model\Source\Category\Status as CategoryStatus;
use Magento\Framework\Exception\LocalizedException;
use Magento\Theme\Block\Html\Title;
use Aheadworks\Blog\Controller\Action;

/**
 * Class View
 * @package Aheadworks\Blog\Controller\Category
 */
class View extends Action
{
    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        if ($categoryId = (int)$this->getRequest()->getParam('blog_category_id')) {
            try {
                $category = $this->categoryRepository->get($categoryId);
                if ($category->getStatus() == CategoryStatus::DISABLED
                    || (!in_array($this->getStoreId(), $category->getStoreIds())
                        && !in_array(0, $category->getStoreIds()))
                ) {
                    return $this->noRoute();
                }

                $title = $category->getMetaTitle() ? $category->getMetaTitle() : $category->getName();
                $resultPage = $this->resultPageFactory->create();
                $pageConfig = $resultPage->getConfig();

                $pageConfig->getTitle()->set($title);
                $pageConfig->setMetadata('description', $category->getMetaDescription());
                /** @var Title $pageTitleBlock */
                $pageTitleBlock = $this->_view->getLayout()->getBlock('page.main.title');
                if ($pageTitleBlock) {
                    $pageTitleBlock->setPageTitle($category->getName());
                }
                return $resultPage;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $this->goBack();
            }
        }

        return $this->noRoute();
    }
}
