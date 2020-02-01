<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\App\Router;

use Magento\Framework\App\RequestInterface;

/**
 * Interface MatcherInterface
 * @package Aheadworks\Blog\App\Router
 */
interface MatcherInterface
{
    /**
     * Match
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function match(RequestInterface $request);
}
