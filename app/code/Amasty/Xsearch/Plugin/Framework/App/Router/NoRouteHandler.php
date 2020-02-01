<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Plugin\Framework\App\Router;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Router\NoRouteHandler as NativeNoRouteHandler;
use Magento\Search\Model\QueryFactory;

class NoRouteHandler
{
    /**
     * @var \Amasty\Xsearch\Helper\Data
     */
    private $config;

    public function __construct(
        \Amasty\Xsearch\Helper\Data $config
    ) {
        $this->config = $config;
    }

    /**
     * @param NativeNoRouteHandler $subject
     * @param $proceed
     * @param RequestInterface $request
     * @return bool
     */
    public function aroundProcess(
        NativeNoRouteHandler $subject,
        \Closure $proceed,
        RequestInterface $request
    ) {
        if ($this->isRedirectEnabled($request)) {
            $pathInfo = $this->getPathInfo($request);
            $request->setParam(QueryFactory::QUERY_VAR_NAME, $pathInfo);
            $request->setModuleName('amasty_xsearch')->setControllerName('redirect')->setActionName('index');

            return true;
        }

        return $proceed($request);
    }

    /**
     * @param RequestInterface $request
     * @return mixed|string
     */
    private function getPathInfo(RequestInterface $request)
    {
        $pathInfo = $request->getOriginalPathInfo() ?: $request->getPathInfo();
        $pathInfo = trim($pathInfo, '/');
        $pathInfo = str_replace('/', ' ', $pathInfo);
        $pathInfo = str_replace('-', ' ', $pathInfo);
        $pathInfo = str_replace('.html', '', $pathInfo);

        return $pathInfo;
    }

    /**
     * @param RequestInterface $request
     * @return bool
     */
    private function isRedirectEnabled(RequestInterface $request)
    {
        $path = $request->getOriginalPathInfo();
        $exp = explode('.', $path);
        $endOfPath = end($exp);

        if ($this->config->getModuleConfig('general/four_zero_four_redirect')
            && !$request->isAjax()
            && (stristr($path, '.') === false || $endOfPath === 'html')
        ) {
            return true;
        }

        return false;
    }
}
