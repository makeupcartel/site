<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\App\Router;

use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;

/**
 * Interface MatcherInterface
 * @package Aheadworks\Blog\App\Router
 */
class MatcherComposite implements MatcherInterface
{
    /**
     * @var array
     */
    private $matchers = [];

    /**
     * @param array $matchers
     */
    public function __construct(array $matchers = [])
    {
        $this->matchers = $matchers;
    }

    /**
     * {@inheritdoc}
     * @param RequestInterface|Http $request
     */
    public function match(RequestInterface $request)
    {
        /** @var MatcherInterface $matcher */
        foreach ($this->matchers as $matcher) {
            if ($matcher instanceof MatcherInterface && $matcher->match($request)) {
                return true;
            }
        }

        return false;
    }
}
