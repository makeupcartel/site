<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Controller\Adminhtml\Category;

use Aheadworks\Blog\Model\ResourceModel\Category\Collection;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassStatus
 * @package Aheadworks\Blog\Controller\Adminhtml\Category
 */
class MassStatus extends AbstractMassAction
{
    /**
     * @inheritdoc
     */
    protected function massAction(Collection $collection)
    {
        $status = (int) $this->getRequest()->getParam('status');
        $changedRecords = 0;

        foreach ($collection->getAllIds() as $categoryId) {
            try {
                $categoryModel = $this->categoryRepository->get($categoryId);
            } catch (\Exception $e) {
                $categoryModel = null;
            }
            if ($categoryModel) {
                $categoryModel->setData('status', $status);
                $this->categoryRepository->save($categoryModel);
                $changedRecords++;
            }
        }
        if ($changedRecords) {
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) were changed.', $changedRecords));
        } else {
            $this->messageManager->addSuccessMessage(__('No records were changed.'));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/');
        return $resultRedirect;
    }
}
