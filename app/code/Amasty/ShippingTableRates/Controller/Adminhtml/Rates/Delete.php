<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Controller\Adminhtml\Rates;

class Delete extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if (!$id) {
            $this->messageManager->addError(__('Unable to find a rate to delete'));
            $this->_redirect('amstrates/methods/index');
            return;
        }

        try {
            /**
             * @var \Amasty\ShippingTableRates\Model\Rate $rate
             */
            $rate = $this->_objectManager->create('Amasty\ShippingTableRates\Model\Rate')->load($id);
            $methodId = $rate->getMethodId();
            $rate->delete();

            $this->messageManager->addSuccess(__('Rate has been deleted'));
            $this->_redirect('amstrates/methods/edit',
                [
                    'id' => $methodId,
                    'tab' => 'rates_section'
                ]
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('amstrates/methods/index');
        }
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_ShippingTableRates::amstrates');
    }
}
