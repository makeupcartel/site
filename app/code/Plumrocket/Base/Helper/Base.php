<?php
/*

Plumrocket Inc.

NOTICE OF LICENSE

This source file is subject to the End-user License Agreement
that is available through the world-wide-web at this URL:
http://wiki.plumrocket.net/wiki/EULA
If you are unable to obtain it through the world-wide-web, please
send an email to support@plumrocket.com so we can send you a copy immediately.

@package    Plumrocket_Base-v2.x.x
@copyright  Copyright (c) 2015-2017 Plumrocket Inc. (http://www.plumrocket.com)
@license    http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement

*/

namespace Plumrocket\Base\Helper;

class Base extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    protected $_storeManager; 

    /**
     * Initialize helper
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager,
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->_objectManager = $objectManager;
        parent::__construct($context);
    }

    /**
     * @return string
     */
    protected function getMktpKey()
    {
        return implode('', array_map('ch'.
            'r', explode('.', '53.51.50.52.49.54.52.56.54.98.53.52.48.101.97.50.97.49.101.53.48.99.52.48.55.48.98.54.55.49.54.49.49.98.52.52.102.53.50.55.49.56')
        ));
    }

    /**
     * @return mixed
     */
    protected function getCurentConfig()
    {
        return $this->getConfig($this->getModName() . '/module/data');
    }

    /**
     * @param $customerKey
     * @return bool|mixed
     */
    protected function getTrueCustomerKey($customerKey)
    {
        $trueKey = false;

        if ($customerKey == $this->getMktpKey()) {
            $trueKey = $this->getCurentConfig();
        }

        return $trueKey ? $trueKey : $customerKey;
    }

    /**
     * @return string
     */
    private function getModName()
    {
        $data = explode("_",  $this->_getModuleName());

        return isset($data[1]) ? $data[1] : '';
    }

    /**
     * @param $customerKey
     * @return bool
     */
    public function isMarketplace($customerKey)
    {
        if ($customerKey == $this->getMktpKey()) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function preparedData()
    {
        $data = [
            'magento_version' => $this->getMagento2Version()
        ];

        return $data;
    }

    /**
     * Receive config section id
     *
     * @return string
     */
    public function getConfigSectionId()
    {
        return $this->_configSectionId;
    }

    /**
     * Receive magento config value
     *
     * @param  string                                     $path
     * @param  string | int                               $store
     * @param  \Magento\Store\Model\ScopeInterface | null $scope
     * @return mixed
     */
    public function getConfig($path, $store = null, $scope = null)
    {
        if ($scope === null) {
            $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        }
        return $this->scopeConfig->getValue($path, $scope, $store);
    }

    /**
     * Receive backtrace
     *
     * @param  string  $title
     * @param  boolean $echo
     * @return string
     */
    public static function backtrace($title = 'Debug Backtrace:', $echo = true)
    {
        $output     = "";
        $output .= "<hr /><div>" . $title . '<br /><table border="1" cellpadding="2" cellspacing="2">';

        $stacks     = debug_backtrace();

        $output .= "<thead><tr><th><strong>File</strong></th><th><strong>Line</strong></th><th><strong>Function</strong></th>".
            "</tr></thead>";
        foreach($stacks as $_stack)
        {
            if (!isset($_stack['file'])) {
                $_stack['file'] = '[PHP Kernel]'; 
            }
            if (!isset($_stack['line'])) {
                $_stack['line'] = ''; 
            }

            $output .=  "<tr><td>{$_stack["file"]}</td><td>{$_stack["line"]}</td>".
                "<td>{$_stack["function"]}</td></tr>";
        }
        $output .=  "</table></div><hr /></p>";
        return $output;
    }

    /**
     * Receive true if Plumrocket module is enabled
     *
     * @param  string $moduleName
     * @return bool
     */
    public function moduleExists($moduleName)
    {
        $hasModule = $this->_moduleManager->isEnabled('Plumrocket_' . $moduleName);
        if ($hasModule) {
            return $this->getModuleHelper($moduleName)->moduleEnabled() ? 2 : 1;
        }

        return false;
    }

    /**
     * Receive helper
     *
     * @param  string $moduleName
     * @return \Magento\Framework\App\Helper\AbstractHelper
     */
    public function getModuleHelper($moduleName)
    {
        return $this->_objectManager->get('Plumrocket\\'. $moduleName .'\Helper\Data');
    }

    /**
     * Magento 2 version
     *
     * @return string
     */
    public function getMagento2Version() 
    {
        $productMetadata = $this->_objectManager->get('Magento\Framework\App\ProductMetadataInterface');

        return $productMetadata->getVersion();
    }

}
