<?php

namespace Unirgy\RapidFlow\Controller\Adminhtml\Profile;


use Unirgy\RapidFlow\Model\Profile;

class ExportXml extends AbstractProfile
{
    public function execute()
    {
        try {
            $fileName = 'rapidflow_profiles.xml';
            /** @var \Unirgy\RapidFlow\Block\Adminhtml\Profile\Grid $grid */
            $grid = $this->_view->getLayout()->createBlock('Unirgy\RapidFlow\Block\Adminhtml\Profile\Grid');
            $content = $grid->getXml();

            $this->_sendUploadResponse($fileName, $content);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_logger->error($e, ['is_exception' => true]);
            $this->_forward('edit');
        }
    }
}
