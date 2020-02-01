<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Megamenu
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Megamenu\Observer;

use Magento\Framework\Event\ObserverInterface;

class Util implements ObserverInterface
{
    /**
     * Core store config.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var storeManager
     */
    protected $_store;

    /**
     * @var \Magento\Framework\Url\ScopeResolverInterface
     */
    protected $_context;

    protected $messageManager;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\Store $store,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->messageManager = $context->getMessageManager();
        $this->_store = $store;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $serial = $this->_scopeConfig->getValue('magedelight/license/serial_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $activation = $this->_scopeConfig->getValue('magedelight/license/activation_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($_SERVER['SERVER_NAME'] != 'localhost' && $_SERVER['SERVER_ADDR'] != '127.0.0.1') {
            if ($serial == '') {
                $this->messageManager->addError(__("Serial key not found. Please enter valid serial key for 'Mega Menu' extension."));
            }
            
            if ($activation == '') {
                $this->messageManager->addError(__("Activation key not found. Please enter valid activation key for 'Mega Menu' extension."));
            }
        }
    }
}
