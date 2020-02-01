<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2019-08-28T11:43:59+00:00
 * File:          app/code/Xtento/ProductExport/Model/Destination/Sftp.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model\Destination;

class Sftp extends AbstractClass
{
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

        if (class_exists('\phpseclib\Net\SFTP')) { // Magento 2.1
            $this->connection = new \phpseclib\Net\SFTP($this->getDestination()->getHostname(), $this->getDestination()->getPort(), $this->getDestination()->getTimeout());
        } elseif (class_exists('\Net_SFTP')) { // Magento 2.0
            $this->connection = new \Net_SFTP($this->getDestination()->getHostname(), $this->getDestination()->getPort(), $this->getDestination()->getTimeout());
        } else {
            $this->getTestResult()->setSuccess(false)->setMessage(
                __('No SFTP functions found. The Net_SFTP class is missing.')
            );
            return false;
        }

        if (!$this->connection) {
            $this->getTestResult()->setSuccess(false)->setMessage(__('Could not connect to SFTP server. Please make sure that there is no firewall blocking the outgoing connection to the SFTP server and that the timeout is set to a high enough value. If this error keeps occurring, please get in touch with your server hoster / server administrator AND with the server hoster / server administrator of the remote SFTP server. A firewall is probably blocking ingoing/outgoing SFTP connections.'));
            return false;
        }

        // Pub/Private key support - make sure to use adjust the loadKey function with the right key format: http://phpseclib.sourceforge.net/documentation/misc_crypt.html WARNING: Magentos version of phpseclib actually only implements CRYPT_RSA_PRIVATE_FORMAT_PKCS1.
        /*$pk = new Crypt_RSA();
        $pk->setPassword($this->getData('password'));
        #$private_key = file_get_contents('c:\\TEMP\\keys\\coreftp_rsa_no_pw.privkey'); // Or load the private key from a file
        $private_key = <<<KEY
-----BEGIN DSA PRIVATE KEY-----
Proc-Type: 4,ENCRYPTED
DEK-Info: DES-EDE3-CBC,F82184195914B351

...................................
-----END DSA PRIVATE KEY-----
KEY;

        if ($pk->loadKey($private_key, CRYPT_RSA_PRIVATE_FORMAT_PKCS1) === false) {
            $this->getTestResult()->setSuccess(false)->setMessage(__('Could not load private key supplied. Make sure it is the PKCS1 format (openSSH) and that the supplied password is right.'));
            return false;
        }*/

        $warning = '';
        $loginResult = false;
        try {
            $loginResult = $this->connection->login($this->getDestination()->getUsername(), $this->encryptor->decrypt($this->getDestination()->getPassword()));
        } catch (\Exception $e) {
            $warning = '(' . __('Detailed Error') . ': ' . substr($e->getMessage(), 0, strrpos($e->getMessage(), ' in ')) . ')';
        }
        if (!$loginResult) {
            #if (!@$this->connection->login($this->getDestination()->getUsername(), $pk)) { // If using pubkey authentication
            $this->getTestResult()->setSuccess(false)->setMessage(__('Connection to SFTP server failed (make sure no firewall is blocking the connection). This error could also be caused by a wrong login for the SFTP server. %1', $warning));
            return false;
        }

        $warning = '';
        $chdirResult = false;
        try {
            $chdirResult = $this->connection->chdir($this->getDestination()->getPath());
        } catch (\Exception $e) {
            $warning = '(' . __('Detailed Error') . ': ' . substr($e->getMessage(), 0, strrpos($e->getMessage(), ' in ')) . ')';
        }
        if (!$chdirResult) {
            $this->getTestResult()->setSuccess(false)->setMessage(__('Could not change directory on SFTP server to import directory. Please make sure the directory exists and that we have rights to read in the directory. %1', $warning));
            return false;
        }

        $this->getTestResult()->setSuccess(true)->setMessage(__('Connection with SFTP server tested successfully.'));
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
            $warning = '';
            $uploadResult = false;
            try {
                $uploadResult = $this->connection->put($filename, $data);
            } catch (\Exception $e) {
                $warning = '(' . __('Detailed Error') . ': ' . substr($e->getMessage(), 0, strrpos($e->getMessage(), ' in ')) . ')';
            }
            if (!$uploadResult) {
                $logEntry->setResult(\Xtento\ProductExport\Model\Log::RESULT_WARNING);
                $message = __("Could not save file %1 in directory %2 on SFTP server %3. Please make sure the directory is writable. Also please make sure that there is no firewall blocking the outgoing connection to the SFTP server. If this error keeps occurring, please get in touch with your server hoster / server administrator AND with the server hoster / server administrator of the remote SFTP server, so they can adjust the firewall. %4", $filename, $this->getDestination()->getPath(), $this->getDestination()->getHostname(), $warning);
                $logEntry->addResultMessage(__('Destination "%1" (ID: %2): %3', $this->getDestination()->getName(), $this->getDestination()->getId(), $message));
                if (!$this->getDestination()->getBackupDestination()) {
                    $this->getDestination()->setLastResultMessage(__($message));
                }
            } else {
                $savedFiles[] = $this->getDestination()->getPath() . $originalFilename;
            }
        }
        return $savedFiles;
    }
}