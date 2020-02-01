<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Controller\Adminhtml\Methods;

class Edit extends \Amasty\ShippingTableRates\Controller\Adminhtml\Methods
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        /**
         * @var \Amasty\ShippingTableRates\Model\Method $model
         */
        $model = $this->_objectManager->create('Amasty\ShippingTableRates\Model\Method');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('Record does not exist.'));
                $this->_redirect('amstrates/*');
                return;
            }
        }
        // set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $this->_coreRegistry->register('current_amasty_table_method', $model);
        $this->_initAction();
        if($model->getId()) {
            $title = __('Edit Method `%1`', $model->getName());
        } else {
            $title = __("Add new Shipping Table Rate");
        }
        $this->_view->getPage()->getConfig()->getTitle()->prepend($title);

        $this->_view->renderLayout();
    }

    protected function prepareForEdit($model)
    {
        $fields = array('stores', 'cust_groups', 'free_types');
        foreach ($fields as $f){
            $val = $model->getData($f);
            if (!is_array($val)){
                $model->setData($f, explode(',', $val));
            }
        }
        return true;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_ShippingTableRates::amstrates');
    }
}
