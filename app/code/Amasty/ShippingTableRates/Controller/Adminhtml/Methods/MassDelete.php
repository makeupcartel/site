<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Controller\Adminhtml\Methods;


class MassDelete extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $ids = $this->getRequest()->getParam('ids');

        try {
            /**
             * @var $collection \Amasty\ShippingTableRates\Model\ResourceModel\Method\Collection
             */
            $collection = $this->_objectManager->create('Amasty\ShippingTableRates\Model\ResourceModel\Method\Collection');
            $collection->addFieldToFilter('id', array('in'=>$ids));
            $collection->walk('delete');
            $this->messageManager->addSuccess(__('Method(s) were successfully deleted'));
        }
        catch (\Exception $e) {
            $this->messageManager->addError(
                __('We can\'t delete method(s) right now. Please review the log and try again. ').$e->getMessage()
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
