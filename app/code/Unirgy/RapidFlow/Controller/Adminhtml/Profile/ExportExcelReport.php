<?php

namespace Unirgy\RapidFlow\Controller\Adminhtml\Profile;



class ExportExcelReport extends AbstractProfile
{
    public function execute()
    {
        $profile = $this->_getProfile();

        try {
            $profile->exportExcelReport();

            $this->_pipeFile(
                $profile->getExcelReportBaseDir() . DIRECTORY_SEPARATOR . $profile->getExcelReportFilename(),
                $profile->getExcelReportFilename(),
                'application/vnd.ms-excel'
            );
        } catch (\Exception $e) {
            $message = $e->getMessage();
            if (strpos($message, 'No such file or directory')) {
                $message = 'Import file not found, cannot generate excel report.';
            }
            $this->messageManager->addErrorMessage($message);
            $this->_logger->error($e, ['is_exception' => true]);
            $this->_forward('edit');
        }
    }
}
