<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\App\Router\Matcher;

use Aheadworks\Blog\App\Router\MatcherInterface;
use Aheadworks\Blog\Model\Config;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Aheadworks\Blog\Model\AuthorFactory;

/**
 * Class AuthorsList
 * @package Aheadworks\Blog\App\Router\Matcher
 */
class AuthorsList implements MatcherInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     * @param RequestInterface|Http $request
     */
    public function match(RequestInterface $request)
    {
        $parts = explode('/', trim($request->getPathInfo(), '/'));
        list(, $urlKey) = array_merge($parts, array_fill(0, 3, null));

        if ($urlKey == $this->config->getRouteToAuthors()) {
            $request
                ->setControllerName('author')
                ->setActionName('list');

            return true;
        }

        return false;
    }
}
