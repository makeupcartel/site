<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Controller\Adminhtml\Methods;


class MassActivate extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $ids = $this->getRequest()->getParam('ids');
        $status = $this->getRequest()->getParam('activate');

        try {
            /**
             * @var \Amasty\ShippingTableRates\Model\Method $methodsModel
             */
            $methodsModel = $this->_objectManager->get('Amasty\ShippingTableRates\Model\Method');
            $methodsModel->massChangeStatus($ids, $status);
            $message = __('Record(s) have been updated.');
            $this->messageManager->addSuccess($message);
        } catch (\Exception $e) {
            $this->messageManager->addError(
                __('We can\'t activate method(s) right now. Please review the log and try again. ') . $e->getMessage()
            );
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }

        $this->_redirect('*/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_ShippingTableRates::amstrates');
    }
}
