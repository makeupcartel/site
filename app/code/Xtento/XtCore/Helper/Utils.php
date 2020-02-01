<?php

/**
 * Product:       Xtento_XtCore
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2019-08-29T15:40:37+00:00
 * File:          app/code/Xtento/XtCore/Helper/Utils.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\XtCore\Helper;

use Magento\Framework\Exception\LocalizedException;

class Utils extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Module List
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Utils constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\ObjectManagerInterface $objectManager

    ) {
        parent::__construct($context);
        $this->moduleList = $moduleList;
        $this->productMetadata = $productMetadata;
        $this->objectManager = $objectManager;
    }

    public function mageVersionCompare($version1, $version2, $operator)
    {
        return version_compare($version1, $version2, $operator);
    }

    /**
     * Checks if an extension is installed and enabled
     *
     * @param $extensionIdentifier
     * @return bool
     */
    public function isExtensionInstalled($extensionIdentifier)
    {
        return $this->moduleList->has($extensionIdentifier);
    }

    /**
     * @param $moduleName
     *
     * @return mixed
     */
    public function getExtensionVersion($moduleName)
    {
        return $this->moduleList->getOne($moduleName)['setup_version'];
    }

    /**
     * Is the module running in a Magento Enterprise Edition installation?
     *
     * @return bool
     */
    public function isMagentoEnterprise()
    {
        return ($this->productMetadata->getEdition() == 'Enterprise' || $this->productMetadata->getEdition() == 'B2B');
    }

    /**
     * Get Magento Version
     */
    public function getMagentoVersion()
    {
        return $this->productMetadata->getVersion();
    }

    /**
     * Either create a ZIP for multiple files or return the filename/file
     *
     * @param $fileArray
     * @return array
     * @throws \Exception
     */
    public function prepareFilesForDownload($fileArray)
    {
        if (count($fileArray) > 1) {
            // We need to zip multiple files and return a ZIP file to browser
            if (!class_exists('ZipArchive')) {
                throw new LocalizedException(__(
                    'PHP ZIP extension not found. Please download files manually from the server, or install the ZIP extension, or export just one file with each profile.'
                ));
            }
            // ZIP creation
            $zipFile = false;
            if (class_exists('ZipArchive')) {
                // Try creating it using the PHP ZIP functions
                $zipArchive = new \ZipArchive();
                $zipFile = tempnam(sys_get_temp_dir(), 'zip');
                if (!$zipFile) {
                    throw new LocalizedException(__(
                        'Could not generate temporary file in tmp folder to store ZIP file. Please contact your hoster and make sure the PHP "tmp" (tempnam(sys_get_temp_dir())) directory is writable. ZIP creation failed.'
                    ));
                }
                if ($zipArchive->open($zipFile, \ZipArchive::CREATE) !== true) {
                    throw new LocalizedException(__('Could not open file ' . $zipFile . '. ZIP creation failed.'));
                }
                foreach ($fileArray as $filename => $content) {
                    $zipArchive->addFromString($filename, $content);
                }
                $zipArchive->close();
            }
            if (!$zipFile) {
                throw new LocalizedException(__('ZIP file couldn\'t be created.'));
            }
            $zipData = file_get_contents($zipFile);
            unlink($zipFile);
            return ['filename' => 'export_' . time() . '.zip', 'data' => $zipData];
        } else {
            // Just one file, output to browser
            foreach ($fileArray as $filename => $content) {
                return ['filename' => $filename, 'data' => $content];
            }
        }
        return [];
    }

    /**
     * @param $moduleName
     * @param $dataModelName
     *
     * @return string
     */
    public function getExtensionStatusString($moduleName, $dataModelName)
    {
        // Set up cache, using the Magento cache doesn't make sense as it won't cache if cache is disabled
        try {
            $cacheBackend = new \Zend_Cache_Backend();
            $cache = \Zend_Cache::factory(
                'Core',
                'File',
                ['lifetime' => 43200],
                ['cache_dir' => $cacheBackend->getTmpDir()]
            );
        } catch (\Exception $e) {
            return '';
        }
        $cacheKey = 'extstatus_' . $moduleName;
        if ($moduleName !== '') {
            $moduleVersion = $this->moduleList->getOne($moduleName)['setup_version'];
            if (!empty($moduleVersion)) {
                $cacheKey .= '_' . str_replace('.', '_', $moduleVersion);
            }
        }
        $cacheKey .= substr(sha1(__DIR__), 0, 10); // Unique per Magento installation
        // Is the response cached?
        $cachedHtml = $cache->load($cacheKey);
        #$cachedHtml = false; // Test: disable cache
        if ($cachedHtml !== false && $cachedHtml !== '') {
            $storeJson = $cachedHtml;
        } else {
            try {
                $dataModel = $this->objectManager->get($dataModelName);
                $dataModel->afterLoad();
                // Fetch info whether updates for the module are available
                $url = 'ht' . 'tp://w' . 'ww.' . 'xte' . 'nto.' . 'co' . 'm/li' . 'cense/status';
                $version = $this->productMetadata->getVersion();
                $extensionVersion = $dataModel->getValue();
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    $streamContext = stream_context_create(['http' => ['timeout' => 5]]);
                    $storeJson = file_get_contents($url . '?version=' . $version . '&d=' . $extensionVersion, false, $streamContext);
                } else {
                    $client = new \Zend_Http_Client($url, ['timeout' => 5]);
                    $client->setParameterGet('version', $version);
                    $client->setParameterGet('d', $extensionVersion);
                    $response = $client->request('GET');
                    // Post version
                    /*$client = new Zend_Http_Client($url, ['timeout' => 5)];
                    $client->setParameterPost('version', $version);
                    $client->setParameterPost('d', $extensionVersion);
                    $response = $client->request('POST');*/
                    $storeJson = $response->getBody();
                }
                $cache->save($storeJson, $cacheKey);
            } catch (\Exception $e) {
                $cache->save('<!-- Empty/error response -->', $cacheKey);
                return '';
            }
        }
        if (preg_match('/There has been an error processing your request/', $storeJson)) {
            return '';
        }
        try {
            $storeJson = json_decode($storeJson, true);
        } catch (\Exception $e) {
            $storeJson = [];
        }
        if (isset($storeJson['html'])) {
            $statusHtml = $storeJson['html'];
        } else {
            return '';
        }
        return $statusHtml;
    }
}
