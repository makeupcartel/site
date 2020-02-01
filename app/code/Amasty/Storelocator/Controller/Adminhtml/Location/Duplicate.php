<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Controller\Adminhtml\Location;

class Duplicate extends \Amasty\Storelocator\Controller\Adminhtml\Location
{
    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('location_id');
        if (!$id) {
            $this->messageManager->addError(__('Please select a location to duplicate.'));
            return $this->_redirect('*/*');
        }
        try {
            $model = $this->_objectManager->create('Amasty\Storelocator\Model\Location')->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                $this->_redirect('*/*');
                return;
            }
            $location = clone $model;
            $location->setStatus(0);
            $location->setId(null);
            $location->save();
            $this->messageManager->addSuccess(__('The location has been duplicated. Please feel free to activate it.'));
            return $this->_redirect('*/*/edit', array('id' => $location->getId()));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('*/*');
            return;
        } catch (\Exception $e) {
            $this->messageManager->addError(
                __('Something went wrong while saving the item data. Please review the error log.')
            );
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $this->_redirect('*/*');
            return;
        }
    }
}