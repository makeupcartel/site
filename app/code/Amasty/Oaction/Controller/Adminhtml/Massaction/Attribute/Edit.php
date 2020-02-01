<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Oaction
 */


namespace Amasty\Oaction\Controller\Adminhtml\Massaction\Attribute;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;

class Edit extends \Amasty\Oaction\Controller\Adminhtml\Massaction
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var \Amasty\Oaction\Model\OrderAttributesChecker
     */
    private $orderAttributesChecker;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        FileFactory $fileFactory,
        PageFactory $pageFactory,
        \Amasty\Oaction\Model\OrderAttributesChecker $orderAttributesChecker
    ) {
        parent::__construct($context, $filter, $collectionFactory, $fileFactory);
        $this->pageFactory = $pageFactory;
        $this->orderAttributesChecker = $orderAttributesChecker;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/order');

        try {
            if ($this->orderAttributesChecker->isModuleExist() && $this->getRequest()->getParam('filters')) {
                $collection = $this->filter->getCollection($this->collectionFactory->create());
                $orderIds = $collection->getAllIds();

                if (!$orderIds) {
                    $this->messageManager->addErrorMessage(__('Please select orders for attributes update.'));

                    return $resultRedirect;
                }

                $this->_objectManager->get(\Amasty\Orderattr\Model\Order\Storage::class)->setOrderIds($orderIds);
            }

            $resultPage = $this->pageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(__('Update Attributes'));

            return $resultPage;
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $resultRedirect;
        }
    }

    /**
     * @param AbstractCollection $collection
     * @return $this
     */
    public function massAction(AbstractCollection $collection)
    {
        return $this;
    }
}
