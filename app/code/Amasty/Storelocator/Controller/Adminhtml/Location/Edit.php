<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Controller\Adminhtml\Location;

class Edit extends \Amasty\Storelocator\Controller\Adminhtml\Location
{
    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Amasty\Storelocator\Model\Location');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                $this->_redirect('*/*');
                return;
            }
        }

        // set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        } else {
            $this->_prepareForEdit($model);
        }

        $this->_coreRegistry->register('current_amasty_storelocator_location', $model);
        $this->_initAction();
        if($model->getId()) {
            $title = __('Edit Store Locator Location `%1`', $model->getName());
        } else {
            $title = __("Add new Location");
        }
        $this->_view->getPage()->getConfig()->getTitle()->prepend($title);
        $this->_view->renderLayout();
    }

    public function _prepareForEdit(\Amasty\Storelocator\Model\Location $model)
    {
        /** @var \Amasty\Storelocator\Model\ResourceModel\Location $locationResource */
        $locationResource = $model->getResource();
        $model = $locationResource->setAttributesData($model);

        if ($model->getSchedule()) {
            $model->setSchedule($this->serializer->unserialize($model->getSchedule()));
        }
        $model->getActions()->setJsFormObject('rule_actions_fieldset');
        return true;
    }
}