<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2019-08-28T11:20:00+00:00
 * File:          app/code/Xtento/ProductExport/Model/Destination/Local.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model\Destination;

class Local extends AbstractClass
{
    public function testConnection()
    {
        $exportDirectory = $this->fixBasePath($this->getDestination()->getPath());
        $testResult = new \Magento\Framework\DataObject();

        // Check for forbidden folders
        $forbiddenFolders = [
            $this->filesystem->getDirectoryRead(
                \Magento\Framework\App\Filesystem\DirectoryList::ROOT
            )->getAbsolutePath()
        ];
        foreach ($forbiddenFolders as $forbiddenFolder) {
            if (realpath($exportDirectory) == $forbiddenFolder) {
                return $testResult->setSuccess(false)->setMessage(
                    __(
                        'It is not allowed to save export files in the directory you have specified. Please change the local export directory to be located in the ./var/ folder for example. Do not use the Magento root directory for example.'
                    )
                );
            }
        }

        if (!is_dir($exportDirectory) && !preg_match('/%exportid%/', $exportDirectory)) {
            // Try to create the directory.
            $warning = '';
            $mkdirResult = false;
            try {
                $mkdirResult = mkdir($exportDirectory);
            } catch (\Exception $e) {
                $warning = '(' . __('Detailed Error') . ': ' . substr($e->getMessage(), 0, strrpos($e->getMessage(), ' in ')) . ')';
            }
            if (!$mkdirResult) {
                return $testResult->setSuccess(false)->setMessage(
                    __(
                        'The specified local directory does not exist. We could not create it either. Please make sure the parent directory is writable or create the directory manually: %1 %2',
                        $exportDirectory, $warning
                    )
                );
            } else {
                $testResult->setDirectoryCreated(true);
            }
        }
        $this->connection = false;
        try {
            $this->connection = opendir($exportDirectory);
        } catch (\Exception $e) {}
        if (!$this->connection || !is_writable($exportDirectory)) {
            return $testResult->setSuccess(false)->setMessage(
                __(
                    'Could not open local export directory for writing. Please make sure that we have rights to read and write in the directory: %1',
                    $exportDirectory
                )
            );
        }
        if ($testResult->getDirectoryCreated()) {
            $testResult->setSuccess(true)->setMessage(
                __('Local directory didn\'t exist and was created successfully. Connection tested successfully.')
            );
            if (!$this->getDestination()->getBackupDestination()) {
                $this->getDestination()->setLastResult($testResult->getSuccess())->setLastResultMessage(
                    $testResult->getMessage()
                )->save();
            }
            return $testResult;
        } else {
            $testResult->setSuccess(true)->setMessage(
                __('Local directory exists and is writable. Connection tested successfully.')
            );
            if (!$this->getDestination()->getBackupDestination()) {
                $this->getDestination()->setLastResult($testResult->getSuccess())->setLastResultMessage(
                    $testResult->getMessage()
                )->save();
            }
            return $testResult;
        }
    }

    public function saveFiles($fileArray)
    {
        if (empty($fileArray)) {
            return [];
        }
        $savedFiles = [];
        $logEntry = $this->_registry->registry('productexport_log');
        // Test connection
        $testResult = $this->testConnection();
        if (!$testResult->getSuccess()) {
            $logEntry->setResult(\Xtento\ProductExport\Model\Log::RESULT_WARNING);
            $logEntry->addResultMessage(
                __(
                    'Destination "%1" (ID: %2): %3',
                    $this->getDestination()->getName(),
                    $this->getDestination()->getId(),
                    $testResult->getMessage()
                )
            );
            if (!$this->getDestination()->getBackupDestination()) {
                $this->getDestination()->setLastResultMessage($testResult->getMessage());
            }
            return false;
        }
        // Save files
        $exportDirectory = $this->fixBasePath($this->getDestination()->getPath());
        foreach ($fileArray as $filename => $data) {
            $originalFilename = $filename;
            if ($this->getDestination()->getBackupDestination()) {
                // Add the export_id as prefix to uniquely store files in the backup/copy folder
                $filename = $logEntry->getId() . '_' . $filename;
            }
            if (preg_match('/%exportid%/', $exportDirectory)) {
                if ($this->_registry->registry('productexport_log')) {
                    $exportId = $this->_registry->registry('productexport_log')->getId();
                } else {
                    $exportId = 0;
                }
                $exportDirectory = preg_replace('/%exportid%/', $exportId, $exportDirectory);
                if (!is_dir($exportDirectory)) {
                    try {
                        mkdir($exportDirectory);
                    } catch (\Exception $e) {}
                }
            }
            $warning = '';
            $filePutContentsResult = false;
            try {
                $filePutContentsResult = file_put_contents($exportDirectory . $filename, $data);
            } catch (\Exception $e) {
                $warning = '(' . __('Detailed Error') . ': ' . substr($e->getMessage(), 0, strrpos($e->getMessage(), ' in ')) . ')';
            }
            if (!$filePutContentsResult && !empty($data)) {
                $logEntry->setResult(\Xtento\ProductExport\Model\Log::RESULT_WARNING);
                $message = __(
                    "Could not save file %1 in directory %2. Please make sure the directory is writable. %3",
                    $filename,
                    $exportDirectory,
                    $warning
                );
                $logEntry->addResultMessage(
                    __(
                        'Destination "%1" (ID: %2): %3',
                        $this->getDestination()->getName(),
                        $this->getDestination()->getId(),
                        $message
                    )
                );
                if (!$this->getDestination()->getBackupDestination()) {
                    $this->getDestination()->setLastResultMessage(__($message));
                }
            } else {
                $savedFiles[] = $exportDirectory . $originalFilename;
            }
        }
        return $savedFiles;
    }

    protected function fixBasePath($originalPath)
    {
        /*
        * Let's try to fix the import directory and replace the dot with the actual Magento root directory.
        * Why? Because if the cronjob is executed using the PHP binary a different working directory (when using a dot (.) in a directory path) could be used.
        * But Magento is able to return the right base path, so let's use it instead of the dot.
        */
        $originalPath = str_replace('/', DIRECTORY_SEPARATOR, $originalPath);
        if (substr($originalPath, 0, 2) == '.' . DIRECTORY_SEPARATOR) {
            return rtrim($this->filesystem->getDirectoryRead(
                \Magento\Framework\App\Filesystem\DirectoryList::ROOT
            )->getAbsolutePath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . substr($originalPath, 2);
        }
        return $originalPath;
    }
}