<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Controller\Adminhtml\Request;

use Amasty\Groupcat\Model\Source\Status;

class Edit extends \Amasty\Groupcat\Controller\Adminhtml\Request
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        try {
            $model = $this->requestRepository->get($id);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage(__('This request no longer exists.'));
            $this->_redirect('amasty_groupcat/*');
            return;
        }

        $this->coreRegistry->register(\Amasty\Groupcat\Controller\Adminhtml\Request::CURRENT_REQUEST_MODEL, $model);

        $this->_initAction();

        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            __('Get a Quote Request from ') . $model->getName()
        );

        $this->_view->renderLayout();

        /* change request status to viewed*/
        if ($model->getStatus() == Status::PENDING) {
            $model->setStatus(Status::VIEWED);
            $this->requestRepository->save($model);
        }
    }
}
