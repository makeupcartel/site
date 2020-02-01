<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Controller\Adminhtml\Rates;

class Edit extends \Magento\Backend\App\Action
{
    protected $_coreRegistry;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        /**
         * @var \Amasty\ShippingTableRates\Model\Rate $objectRate
         */
        $objectRate = $this->_objectManager->create('Amasty\ShippingTableRates\Model\Rate')->load($id);
        $methodId = $this->getRequest()->getParam('method_id');

        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        if (!empty($data)) {
            $objectRate->setData($data);
        }

        if ($methodId && !$objectRate->getId()){
            $objectRate->setMethodId($methodId);
            $objectRate->setWeightFrom('0');
            $objectRate->setQtyFrom('0');
            $objectRate->setPriceFrom('0');
            $objectRate->setWeightTo($objectRate::MAX_VALUE);
            $objectRate->setQtyTo($objectRate::MAX_VALUE);
            $objectRate->setPriceTo($objectRate::MAX_VALUE);
        }

        $this->_coreRegistry->register('amtable_rate', $objectRate);

        $this->_view->loadLayout();

        $this->_setActiveMenu('Amasty_ShippingTableRates::amstrates')->_addBreadcrumb(__('Table Rates'), __('Table Rates'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend('Rate Configuration');

        $this->_view->renderLayout();
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_ShippingTableRates::amstrates');
    }
}
