<?php

namespace Unirgy\RapidFlow\Controller\Adminhtml\Profile;


use Unirgy\RapidFlow\Model\Profile;
use Unirgy\RapidFlow\Block\Adminhtml\Profile\Grid;

class ExportCsv extends AbstractProfile
{
    public function execute()
    {
        try {
            $fileName = 'urapidflow_profiles.csv';
            /** @var \Unirgy\RapidFlow\Block\Adminhtml\Profile\Grid $grid */
            $grid = $this->_view->getLayout()->createBlock(Grid::class);
            $content = $grid->getCsv();

            $this->_sendUploadResponse($fileName, $content);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_logger->error($e, ['is_exception' => true]);
            $this->_forward('edit');
        }
    }
}
