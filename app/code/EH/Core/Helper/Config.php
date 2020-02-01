<?php
/**
 * @author ExtensionHut Team
 * @copyright Copyright (c) 2018 ExtensionHut (https://www.extensionhut.com/)
 * @package EH_Core
 */

namespace EH\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\DataObject;

class Config extends AbstractHelper {

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var int
     */
    protected $_storeId;

    /**
     * @var data object
     */
    protected $_config;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Get store config
     *
     * @return data object
     */
    public function getConfig($configBasePath, $scope = ScopeInterface::SCOPE_STORE, $storeId = null) {
        if(!$this->_config) {
            if(!$storeId) {
                $storeId = $this->getStoreId();
            }

            $configs = $this->scopeConfig->getValue(
                $configBasePath,
                $scope,
                $storeId
            );
            $settings = [];
            foreach ($configs as $node => $config) {
                $settings[$node] = new DataObject($config);
            }
            $this->_config = new DataObject($settings);
        }
        return $this->_config;
    }

    /**
     * Get current store id
     *
     * @return int
     */
    public function getStoreId()
    {
        if(is_null($this->_storeId)) {
            $this->_storeId = intval($this->_storeManager->getStore()->getId());
        }

        return $this->_storeId;
    }
}
