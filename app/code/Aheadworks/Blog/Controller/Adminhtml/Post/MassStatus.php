<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Controller\Adminhtml\Post;

use Aheadworks\Blog\Model\ResourceModel\Post\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Aheadworks\Blog\Api\PostRepositoryInterface;
use Aheadworks\Blog\Model\ResourceModel\Post\Collection;
use Aheadworks\Blog\Model\Source\Post\Status;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class MassStatus
 * @package Aheadworks\Blog\Controller\Adminhtml\Post
 */
class MassStatus extends AbstractMassAction
{
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param PostRepositoryInterface $postRepository
     * @param DateTime $dateTime
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        PostRepositoryInterface $postRepository,
        DateTime $dateTime
    ) {
        $this->dateTime = $dateTime;
        parent::__construct($context, $filter, $collectionFactory, $postRepository);
    }

    /**
     * @inheritdoc
     */
    protected function massAction(Collection $collection)
    {
        $status = $this->getRequest()->getParam('status');
        $changedRecords = 0;

        foreach ($collection->getAllIds() as $postId) {
            try {
                $postModel = $this->postRepository->get($postId);
            } catch (\Exception $e) {
                $postModel = null;
            }
            if ($postModel) {
                $postModel->setData('status', $status);
                if ($status == Status::PUBLICATION) {
                    $postModel->setData(
                        'publish_date',
                        $this->dateTime->gmtDate(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
                    );
                }
                $this->postRepository->save($postModel);
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
