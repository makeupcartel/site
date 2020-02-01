<?php

/**
 * Product:       Xtento_XtCore
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2019-05-17T11:28:59+00:00
 * File:          app/code/Xtento/XtCore/Block/System/Config/Form/Xtento/Debug.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\XtCore\Block\System\Config\Form\Xtento;

use Magento\Framework\App\Filesystem\DirectoryList;

class Debug extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    protected $directory;

    /**
     * @var \Magento\Framework\HTTP\ZendClientFactory
     */
    protected $zendClientFactory;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\View\Helper\Js $jsHelper
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Http\ZendClientFactory $httpClientFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->zendClientFactory = $httpClientFactory;
    }

    /*
     * Debug information is shown at System > Configuration > XTENTO Extensions > General Configuration
     */
    protected function _getHeaderHtml($element)
    {
        $headerHtml = parent::_getHeaderHtml($element);
        $debugInfo = [];
        try {
            // Fetch public IP address of server - important if you have failing FTP transfers
            // and need to add the public IP address to the firewall, etc.
            $url = 'https://www.xtento.com/license/info/getip';
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $ipAddress = file_get_contents($url);
            } else {
                $client = $this->zendClientFactory->create();
                $client->setUri($url);
                $client->setConfig(['timeout' => 10]);
                $response = $client->request('GET');
                $ipAddress = $response->getBody();
            }
        } catch (\Exception $e) {
            return '------------------------------------------------<div style="display:none">Exception: ' .
                $e->getMessage() . '</div>' . $headerHtml;
        }

        $debugInfo[] = "Public Server IP Address: $ipAddress<br/>";
        $debugInfo[] = "PHP Version: ".phpversion();
        $debugInfo[] = "PHP memory_limit: " . ini_get('memory_limit');
        $debugInfo[] = "PHP max_execution_time: " . ini_get('max_execution_time');
        $debugInfo[] = "Magento Base Path: " . $this->directory->getAbsolutePath();

        // PHP Info
        ob_start();
        phpinfo();
        $phpinfoString = ob_get_contents();
        ob_get_clean();
        $phpinfoString = preg_replace('#^.*<body>(.*)</body>.*$#ms', '$1', $phpinfoString);
        $phpinfoString = preg_replace('#>(on|enabled|active)#i', '><span style="color:#090">$1</span>', $phpinfoString);
        $phpinfoString = preg_replace('#>(off|disabled)#i', '><span style="color:#f00">$1</span>', $phpinfoString);
        $phpinfoString = "
                <style type='text/css'>
                    #phpinfo { margin-top: 15px; }
                    #phpinfo pre {margin: 0; font-family: monospace;}
                    #phpinfo a:link {color: #009; text-decoration: none; background-color: #fff;}
                    #phpinfo a:hover {text-decoration: underline;}
                    #phpinfo table {border-collapse: collapse; border: 0; width: 98%; box-shadow: 1px 2px 3px #ccc;}
                    #phpinfo .center {text-align: center;}
                    #phpinfo .center table {margin: 1em auto; text-align: left;}
                    #phpinfo .center th {text-align: center !important;}
                    #phpinfo td, th {border: 1px solid #666; font-size: 75%; vertical-align: baseline; padding: 4px 5px;}
                    #phpinfo h1 {font-size: 150%;}
                    #phpinfo h2 {font-size: 125%;}
                    #phpinfo .p {text-align: left;}
                    #phpinfo .e {background-color: #ccf; width: 300px; font-weight: bold;}
                    #phpinfo .h {background-color: #99c; font-weight: bold;}
                    #phpinfo .v {background-color: #ddd; max-width: 300px; overflow-x: auto; word-wrap: break-word;}
                    #phpinfo .v i {color: #999;}
                    #phpinfo img {float: right; border: 0;}
                    #phpinfo hr {width: 98%; background-color: #ccc; border: 0; height: 1px;}
                </style>
                <div id='phpinfo'>
                    $phpinfoString
                </div>
                ";
        $debugInfo[] = $phpinfoString;

        $headerHtml = str_replace(
            '<table cellspacing="0" class="form-list">',
            implode("<br/>", $debugInfo) . '<table cellspacing="0" class="form-list">',
            $headerHtml
        );
        return $headerHtml;
    }
}
