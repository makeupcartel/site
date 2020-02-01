<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Controller\Adminhtml\Category;

use Aheadworks\Blog\Api\CategoryRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Edit
 * @package Aheadworks\Blog\Controller\Adminhtml\Category
 */
class Edit extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Aheadworks_Blog::categories';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CategoryRepositoryInterface $categoryRepository
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Category edit action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $categoryId = (int)$this->getRequest()->getParam('id');
        if ($categoryId) {
            try {
                $this->categoryRepository->get($categoryId);
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addException($exception, __('Something went wrong while editing the category.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/');
                return $resultRedirect;
            }
        }
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage
            ->setActiveMenu('Aheadworks_Blog::categories')
            ->getConfig()->getTitle()->prepend(
                $categoryId ?  __('Edit Category') : __('New Category')
            );
        return $resultPage;
    }
}
