<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Controller;

use Magento\Framework\Module\Manager;

class Router implements \Magento\Framework\App\RouterInterface
{
    /** @var \Magento\Framework\App\ActionFactory */
    protected $_actionFactory;

    /** @var \Magento\Framework\App\ResponseInterface */
    protected $_response;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /** @var  Manager */
    protected $_moduleManager;

    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Manager $moduleManager)
    {
        $this->_actionFactory = $actionFactory;
        $this->_response = $response;
        $this->_scopeConfig = $scopeConfig;
        $this->_moduleManager = $moduleManager;
    }

    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $locatorPage = $this->_scopeConfig->getValue(
            'amlocator/general/url',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $identifier = trim($request->getPathInfo(), '/');
        $identifier = current(explode("/", $identifier));

        if($identifier == $locatorPage) {
            // Forward Shopby
            $request->setModuleName('amlocator')->setControllerName('index')->setActionName('index');
            $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);
            return $this->_actionFactory->create('Magento\Framework\App\Action\Forward');
        }
    }
}
