<?php

namespace Convert\CustomerContent\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;

class Delete extends Action
{
    /**
     * @var \Convert\CustomerContent\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * Delete constructor.
     * @param Action\Context $context
     * @param \Convert\CustomerContent\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
        Action\Context $context,
        \Convert\CustomerContent\Model\CategoryFactory $categoryFactory
    ) {
        parent::__construct($context);
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $categoryId = (int) $this->getRequest()->getParam('category_id');

        if ($categoryId) {
            /** @var $categoryModel \Convert\CustomerContent\Model\Category */
            $categoryModel = $this->categoryFactory->create();
            $categoryModel->load($categoryId);

            // Check this category exists or not
            if (!$categoryModel->getId()) {
                $this->messageManager->addError(__('This category no longer exists.'));
            } else {
                try {
                    // Delete category
                    $categoryModel->delete();
                    $this->messageManager->addSuccess(__('The category has been deleted.'));

                    // Redirect to grid page
                    $this->_redirect('*/*/');
                    return;
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                    $this->_redirect('*/*/edit', ['id' => $categoryModel->getId()]);
                }
            }
        }
    }
}