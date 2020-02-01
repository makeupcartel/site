<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Controller;

use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\RequestInterface;
use Magento\Search\Model\QueryFactory;

class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    private $actionFactory;

    /**
     * @var \Amasty\Xsearch\Helper\Data
     */
    private $helper;

    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Amasty\Xsearch\Helper\Data $helper
    ) {
        $this->actionFactory = $actionFactory;
        $this->helper = $helper;
    }

    public function match(RequestInterface $request)
    {
        $seoKey = $this->helper->getSeoKey();
        if ($this->helper->isSeoUrlsEnabled() && $seoKey) {
            $identifier = trim($request->getPathInfo(), '/');
            $urlParams = explode('/', $identifier);

            if (!empty($urlParams) && $urlParams[0] == $seoKey) {
                array_shift($urlParams);
                $request->setModuleName('catalogsearch')->setControllerName('result')->setActionName('index');
                $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);
                $params = $request->getParams();
                $params[QueryFactory::QUERY_VAR_NAME] = urldecode(implode('/', $urlParams));
                $request->setParams($params);
                $request->setForwarded(true);

                return $this->actionFactory->create(Forward::class);
            }
        }

        return false;
    }
}
