<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for tag cloud item search results
 * @api
 */
interface TagCloudItemSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get tags list
     *
     * @return \Aheadworks\Blog\Api\Data\TagCloudItemInterface[]
     */
    public function getItems();

    /**
     * Set tags list
     *
     * @param \Aheadworks\Blog\Api\Data\TagCloudItemInterface[] $items
     * @return $this
     */
    public function setItems(array $items);

    /**
     * Get maximal number of posts
     *
     * @return int
     */
    public function getMaxPostCount();

    /**
     * Set maximal number of posts
     *
     * @param int $maxPostCount
     * @return $this
     */
    public function setMaxPostCount($maxPostCount);

    /**
     * Get minimal number of posts
     *
     * @return int
     */
    public function getMinPostCount();

    /**
     * Set minimal number of posts
     *
     * @param int $minPostCount
     * @return $this
     */
    public function setMinPostCount($minPostCount);
}
