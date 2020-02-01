<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2019-08-28T10:02:18+00:00
 * File:          app/code/Xtento/ProductExport/Model/Destination/Ftp.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model\Destination;

class Ftp extends AbstractClass
{
    const TYPE_FTP = 'ftp';
    const TYPE_FTPS = 'ftps';

    public function testConnection()
    {
        $this->initConnection();
        if (!$this->getDestination()->getBackupDestination()) {
            $this->getDestination()->setLastResult($this->getTestResult()->getSuccess())->setLastResultMessage($this->getTestResult()->getMessage())->save();
        }
        return $this->getTestResult();
    }

    public function initConnection()
    {
        $this->setDestination($this->destinationFactory->create()->load($this->getDestination()->getId()));
        $testResult = new \Magento\Framework\DataObject();
        $this->setTestResult($testResult);

        $this->connection = false;
        $warning = '';
        if ($this->getDestination()->getFtpType() == self::TYPE_FTPS) {
            if (function_exists('ftp_ssl_connect')) {
                try {
                    $this->connection = ftp_ssl_connect($this->getDestination()->getHostname(), $this->getDestination()->getPort(), $this->getDestination()->getTimeout());
                } catch (\Exception $e) {
                    $warning = '(' . __('Detailed Error') . ': ' . substr($e->getMessage(), 0, strrpos($e->getMessage(), ' in ')) . ')';
                }
            } else {
                $this->getTestResult()->setSuccess(false)->setMessage(__('No FTP-SSL functions found. Please compile PHP with SSL support.'));
                return false;
            }
        } else {
            if (function_exists('ftp_connect')) {
                try {
                    $this->connection = ftp_connect($this->getDestination()->getHostname(), $this->getDestination()->getPort(), $this->getDestination()->getTimeout());
                } catch (\Exception $e) {
                    $warning = '(' . __('Detailed Error') . ': ' . substr($e->getMessage(), 0, strrpos($e->getMessage(), ' in ')) . ')';
                }
            } else {
                $this->getTestResult()->setSuccess(false)->setMessage(__('No FTP functions found. Please compile PHP with FTP support.'));
                return false;
            }
        }

        if (!$this->connection) {
            $this->getTestResult()->setSuccess(false)->setMessage(__('Could not connect to FTP server. Please make sure that there is no firewall blocking the outgoing connection to the FTP server and that the timeout is set to a high enough value. If this error keeps occurring, please get in touch with your server hoster / server administrator AND with the server hoster / server administrator of the remote FTP server. A firewall is probably blocking ingoing/outgoing FTP connections. %1', $warning));
            return false;
        }

        $warning = '';
        $loginResult = false;
        try {
            $loginResult = ftp_login($this->connection, $this->getDestination()->getUsername(), $this->encryptor->decrypt($this->getDestination()->getPassword()));
        } catch (\Exception $e) {
            $warning = '(' . __('Detailed Error') . ': ' . substr($e->getMessage(), 0, strrpos($e->getMessage(), ' in ')) . ')';
        }
        if (!$loginResult) {
            $this->getTestResult()->setSuccess(false)->setMessage(__('Could not log into FTP server. Wrong username or password. %1', $warning));
            return false;
        }

        if ($this->getDestination()->getFtpPasv()) {
            // Enable passive mode
            try {
                if (!ftp_pasv($this->connection, true)) {
                    #$this->getTestResult()->setSuccess(false)->setMessage(__('Could not enable passive mode for FTP connection.'));
                    #$this->getDestination()->setLastResult($this->getTestResult()->getSuccess())->setLastResultMessage($this->getTestResult()->getMessage())->save();
                    #return false;
                }
            } catch (\Exception $e) {}
        }

        $warning = '';
        $chdirResult = false;
        try {
            $chdirResult = ftp_chdir($this->connection, $this->getDestination()->getPath());
        } catch (\Exception $e) {
            $warning = '(' . __('Detailed Error') . ': ' . substr($e->getMessage(), 0, strrpos($e->getMessage(), ' in ')) . ')';
        }
        if (!$chdirResult) {
            $this->getTestResult()->setSuccess(false)->setMessage(__('Could not change directory on FTP server to export directory. Please make sure the directory exists (base path must be exactly the same) and that we have rights to read in the directory. %1', $warning));
            return false;
        }

        $this->getTestResult()->setSuccess(true)->setMessage(__('Connection with FTP server tested successfully.'));
        return true;
    }


    public function saveFiles($fileArray)
    {
        if (empty($fileArray)) {
            return [];
        }
        $savedFiles = [];
        $logEntry = $this->_registry->registry('productexport_log');
        // Test & init connection
        $this->initConnection();
        if (!$this->getTestResult()->getSuccess()) {
            $logEntry->setResult(\Xtento\ProductExport\Model\Log::RESULT_WARNING);
            $logEntry->addResultMessage(__('Destination "%1" (ID: %2): %3', $this->getDestination()->getName(), $this->getDestination()->getId(), $this->getTestResult()->getMessage()));
            return false;
        }

        // Save files
        foreach ($fileArray as $filename => $data) {
            $originalFilename = $filename;
            if ($this->getDestination()->getBackupDestination()) {
                // Add the export_id as prefix to uniquely store files in the backup/copy folder
                $filename = $logEntry->getId() . '_' . $filename;
            }
            $tempHandle = fopen('php://temp', 'r+');
            fwrite($tempHandle, $data);
            rewind($tempHandle);

            $warning = '';
            $uploadResult = false;
            try {
                $uploadResult = ftp_fput($this->connection, $filename, $tempHandle, FTP_BINARY);
            } catch (\Exception $e) {
                $warning = '(' . __('Detailed Error') . ': ' . substr($e->getMessage(), 0, strrpos($e->getMessage(), ' in ')) . ')';
            }
            if (!$uploadResult) {
                $logEntry->setResult(\Xtento\ProductExport\Model\Log::RESULT_WARNING);
                $message = sprintf("Could not save file %1 in directory %2 on FTP server %3. You can try enabling passive mode in the configuration. Please make sure the directory is writable. Also please make sure that there is no firewall blocking the outgoing connection to the FTP server. If this error keeps occurring, please get in touch with your server hoster / server administrator AND with the server hoster / server administrator of the remote FTP server, so they can adjust the firewall. %4", $filename, $this->getDestination()->getPath(), $this->getDestination()->getHostname(), $warning);
                $logEntry->addResultMessage(__('Destination "%1" (ID: %2): %3', $this->getDestination()->getName(), $this->getDestination()->getId(), $message));
                if (!$this->getDestination()->getBackupDestination()) {
                    $this->getDestination()->setLastResultMessage(__($message));
                }
            } else {
                $savedFiles[] = $this->getDestination()->getPath() . $originalFilename;
            }
        }
        try {
            ftp_close($this->connection);
        } catch (\Exception $e) {}
        return $savedFiles;
    }
}