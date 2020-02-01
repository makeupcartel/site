<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for post search results
 * @api
 */
interface PostSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get posts list
     *
     * @return \Aheadworks\Blog\Api\Data\PostInterface[]
     */
    public function getItems();

    /**
     * Set posts list
     *
     * @param \Aheadworks\Blog\Api\Data\PostInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
