<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\App;

use Aheadworks\Blog\App\Router\MatcherComposite;
use Aheadworks\Blog\Model\Config;
use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\App\ActionFactory;

/**
 * Blog Router
 * @package Aheadworks\Blog\App
 */
class Router implements RouterInterface
{
    /**
     * @var ActionFactory
     */
    private $actionFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var MatcherComposite
     */
    private $matcherComposite;

    /**
     * @param ActionFactory $actionFactory
     * @param Config $config
     * @param MatcherComposite $matcherComposite
     */
    public function __construct(
        ActionFactory $actionFactory,
        Config $config,
        MatcherComposite $matcherComposite
    ) {
        $this->actionFactory = $actionFactory;
        $this->config = $config;
        $this->matcherComposite = $matcherComposite;
    }

    /**
     * Match blog pages
     *
     * {@inheritdoc}
     * @param RequestInterface|Http $request
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function match(RequestInterface $request)
    {
        if (!$this->config->isBlogEnabled()) {
            return false;
        }
        $parts = explode('/', trim($request->getPathInfo(), '/'));
        if (array_shift($parts) != $this->config->getRouteToBlog()) {
            return false;
        }
        $request->setModuleName('aw_blog');

        if (count($parts)) {
            if (!$this->matcherComposite->match($request)) {
                $request
                    ->setModuleName('cms')
                    ->setControllerName('noroute')
                    ->setActionName('index');
            }
        } else {
            $request
                ->setControllerName('index')
                ->setActionName('index');
        }

        return $this->actionFactory->create(Forward::class);
    }
}
