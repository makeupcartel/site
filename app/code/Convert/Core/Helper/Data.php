<?php
/**
 * ConvertDigital - Core Module version 2.0.0 for Magento 2
 *
 * @category  Integration
 * @package   Convert
 * @author    Convert Digital
 * @copyright 2018 Convert Digital
 * @version   2.0.0
 *
 */
namespace Convert\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Data
 * @package Convert\Core\Helper
 */
class Data extends AbstractHelper
{

    const XML_PATH_CORE_DEBUG = 'convert_core/general/debug';

    /**
     * @var string
     */
    protected $_moduleCode = 'Convert_Core';

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
    }

    /**
     * @param $configPath
     * @return mixed
     */
    public function getConfig($configPath)
    {
        return $this->scopeConfig->getValue($configPath,ScopeInterface::SCOPE_STORE);
    }

    /**
     * Check if the debug mode is enabled
     *
     * @return mixed
     */
    public function isDebug()
    {
        return $this->getConfig(self::XML_PATH_CORE_DEBUG);
    }

    /**
     * log error message
     *
     * @param string $type
     * @param string $message
     * @param array $context
     */
    public function logMessage($type, $message, $context = array())
    {
        if (!$this->isDebug()) {
            return;
        }
        $message = $this->_moduleCode . ': ' . $message;
        switch ($type) {
            case 'debug':
                $this->_logger->debug($message, $context);
                break;
            case 'info':
                $this->_logger->info($message, $context);
                break;
            case 'warning':
                $this->_logger->warning($message, $context);
                break;
            case 'critical':
                $this->_logger->critical($message, $context);
                break;
        }
    }
}
