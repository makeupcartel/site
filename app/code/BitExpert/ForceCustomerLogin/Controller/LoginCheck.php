<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Controller;

use BitExpert\ForceCustomerLogin\Api\Controller\LoginCheckInterface;
use BitExpert\ForceCustomerLogin\Api\Repository\WhitelistRepositoryInterface;
use BitExpert\ForceCustomerLogin\Helper\Strategy\StrategyManager;
use BitExpert\ForceCustomerLogin\Model\Session;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class LoginCheck
 *
 * @package BitExpert\ForceCustomerLogin\Controller
 */
class LoginCheck extends Action implements LoginCheckInterface
{
    /**
     * @var UrlInterface
     */
    private $url;
    /**
     * @var CustomerSession
     */
    private $customerSession;
    /**
     * @var Session
     */
    private $session;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var WhitelistRepositoryInterface
     */
    private $whitelistRepository;
    /**
     * @var StrategyManager
     */
    private $strategyManager;
    /**
     * @var ModuleCheck
     */
    private $moduleCheck;
    /**
     * @var ResponseHttp
     */
    private $response;

    /**
     * Creates a new {@link \BitExpert\ForceCustomerLogin\Controller\LoginCheck}.
     *
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param Session $session
     * @param ScopeConfigInterface $scopeConfig
     * @param WhitelistRepositoryInterface $whitelistRepository
     * @param StrategyManager $strategyManager
     * @param ModuleCheck $moduleCheck
     * @param ResponseHttp $response
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        Session $session,
        ScopeConfigInterface $scopeConfig,
        WhitelistRepositoryInterface $whitelistRepository,
        StrategyManager $strategyManager,
        ModuleCheck $moduleCheck,
        ResponseHttp $response
    ) {
        $this->customerSession = $customerSession;
        $this->session = $session;
        $this->scopeConfig = $scopeConfig;
        $this->whitelistRepository = $whitelistRepository;
        $this->strategyManager = $strategyManager;
        $this->moduleCheck = $moduleCheck;
        $this->response = $response;
        parent::__construct($context);
    }

    /**
     * Manages redirect
     *
     * @return bool TRUE if redirection is applied, else FALSE
     */
    public function execute()
    {
        if ($this->moduleCheck->isModuleEnabled() === false) {
            return false;
        }

        // if user is logged in, every thing is fine
        if ($this->customerSession instanceof \Magento\Customer\Model\Session &&
            $this->customerSession->isLoggedIn()) {
            return false;
        }

        $url = $this->_url->getCurrentUrl();
        $path = \parse_url($url, PHP_URL_PATH);
        $targetUrl = $this->getTargetUrl();

        // current path is already pointing to target url, no redirect needed
        if ($targetUrl === $path) {
            return false;
        }

        // check if current url is a match with one of the ignored urls
        foreach ($this->whitelistRepository->getCollection()->getItems() as $rule) {
            /** @var $rule \BitExpert\ForceCustomerLogin\Model\WhitelistEntry */
            $strategy = $this->strategyManager->get($rule->getStrategy());
            if ($strategy->isMatch($path, $rule)) {
                return false;
            }
        }

        if (!$this->isAjaxRequest()) {
            $this->session->setAfterLoginReferer($path);
        }

        $this->response->setNoCacheHeaders();
        $this->response->setRedirect($this->getRedirectUrl($targetUrl));
        $this->response->sendResponse();
        return true;
    }

    /**
     * @return string
     */
    private function getTargetUrl()
    {
        return $this->scopeConfig->getValue(
            self::MODULE_CONFIG_TARGET,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if a request is AJAX request
     *
     * @return bool
     */
    private function isAjaxRequest()
    {
        if ($this->_request instanceof \Magento\Framework\App\Request\Http) {
            return $this->_request->isAjax();
        }
        if ($this->_request->getParam('ajax') || $this->_request->getParam('isAjax')) {
            return true;
        }
        return false;
    }

    /**
     * @param string $targetUrl
     * @return string
     */
    private function getRedirectUrl($targetUrl)
    {
        return \sprintf(
            '%s%s',
            $this->_url->getBaseUrl(),
            $targetUrl
        );
    }
}
