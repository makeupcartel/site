<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\ResourceModel\TagCloudItem;

use Aheadworks\Blog\Api\Data\TagCloudItemSearchResultsInterface;

/**
 * SearchResults for tag cloud items
 */
class SearchResults extends \Magento\Framework\Api\SearchResults implements TagCloudItemSearchResultsInterface
{
    const KEY_MAX_POST_COUNT = 'max_post_count';
    const KEY_MIN_POST_COUNT = 'min_post_count';

    /**
     * {@inheritdoc}
     */
    public function getMaxPostCount()
    {
        return $this->_get(self::KEY_MAX_POST_COUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setMaxPostCount($maxPostCount)
    {
        return $this->setData(self::KEY_MAX_POST_COUNT, $maxPostCount);
    }

    /**
     * {@inheritdoc}
     */
    public function getMinPostCount()
    {
        return $this->_get(self::KEY_MIN_POST_COUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setMinPostCount($minPostCount)
    {
        return $this->setData(self::KEY_MIN_POST_COUNT, $minPostCount);
    }
}
