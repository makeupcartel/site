<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Controller\Adminhtml\Methods;

class Delete extends \Amasty\ShippingTableRates\Controller\Adminhtml\Methods
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        /**
         * @var \Amasty\ShippingTableRates\Model\Method $model
         */
        $model = $this->_objectManager->create('Amasty\ShippingTableRates\Model\Method')->load($id);

        if ($id && !$model->getId()) {
            $this->messageManager->addError(__('Record does not exist'));
            $this->_redirect('*/*/');
            return;
        }

        try {
            $model->delete();
            $this->messageManager->addSuccess(
                __('Shipping method has been successfully deleted'));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        $this->_redirect('*/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_ShippingTableRates::amstrates');
    }
}
