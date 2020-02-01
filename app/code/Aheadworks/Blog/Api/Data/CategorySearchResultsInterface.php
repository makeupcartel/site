<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for category search results
 * @api
 */
interface CategorySearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get categories list
     *
     * @return \Aheadworks\Blog\Api\Data\CategoryInterface[]
     */
    public function getItems();

    /**
     * Set categories list
     *
     * @param \Aheadworks\Blog\Api\Data\CategoryInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
