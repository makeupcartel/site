<?php

namespace Unirgy\RapidFlow\Controller\Adminhtml\Profile;

class DownloadLog extends AbstractProfile
{
    public function execute()
    {
        try {
            $profile = $this->_getProfile();

            $this->_pipeFile(
                $profile->getLogBaseDir() . DIRECTORY_SEPARATOR . $profile->getLogFilename(),
                $profile->getLogFilename(),
                'application/vnd.ms-excel'
            );
        } catch (\Exception $e) {
            $message = $e->getMessage();
            if (strpos($message, 'No such file or directory')) {
                $message = 'Log file not found.';
            }
            $this->messageManager->addErrorMessage($message);
            $this->_logger->error($e, ['is_exception' => true]);
            $this->_forward('edit');
        }
    }
}
