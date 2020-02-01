<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Sitemap\ItemsProvider;

/**
 * Interface ProviderInterface
 * @package Aheadworks\Blog\Model\Sitemap\ItemsProvider
 */
interface ProviderInterface
{
    /**
     * Retrieve sitemap items
     *
     * @param int $storeId
     * @return array
     */
    public function getItems($storeId);

    /**
     * Retrieve sitemap items 2.3.x compatibility
     *
     * @param int $storeId
     * @return array
     */
    public function getItems23x($storeId);
}
