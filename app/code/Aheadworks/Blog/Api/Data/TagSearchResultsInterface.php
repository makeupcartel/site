<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for tag search results
 * @api
 */
interface TagSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get tags list
     *
     * @return \Aheadworks\Blog\Api\Data\TagInterface[]
     */
    public function getItems();

    /**
     * Set tags list
     *
     * @param \Aheadworks\Blog\Api\Data\TagInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
