<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Controller\Adminhtml\Location;

class MassAction extends \Amasty\Storelocator\Controller\Adminhtml\Location
{
    public function execute()
    {
        $filter = $this->_objectManager->create('Magento\Ui\Component\MassAction\Filter');
        /** @var \Magento\Ui\Component\MassAction\Filter $filter */
        $filter->applySelectionOnTargetProvider(); // compatibility with Mass Actions on Magento 2.1.0
        /**
         * @var $collection \Amasty\Storelocator\Model\ResourceModel\Location\Collection
         */
        $collection = $this->_objectManager->create('Amasty\Storelocator\Model\ResourceModel\Location\Collection');
        $collection = $filter->getCollection($collection);

        $collectionSize = $collection->getSize();
        $action = $this->getRequest()->getParam('action');
        if ($collectionSize && in_array($action, ['activate', 'inactivate', 'delete'])) {
            try {
                $collection->walk($action);
                if ($action == 'delete') {
                    $this->messageManager->addSuccess(__('You deleted the location(s).'));
                } else {
                    $this->messageManager->addSuccess(__('You changed the location(s).'));
                }
                $this->_redirect('*/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('We can\'t delete location(s) right now. Please review the log and try again.').$e->getMessage()
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_redirect('*/*/');
                return;
            }
        }
        $this->messageManager->addError(__('We can\'t find a location(s) to delete.'));
        $this->_redirect('*/*/');
    }
}