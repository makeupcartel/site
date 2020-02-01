<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Tag Cloud Item repository interface
 * @api
 */
interface TagCloudItemRepositoryInterface
{
    /**
     * Retrieve tag cloud item
     *
     * @param int $tagId
     * @param int $storeId
     * @return \Aheadworks\Blog\Api\Data\TagCloudItemInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($tagId, $storeId);

    /**
     * Retrieve tags cloud items matching the specified criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param int $storeId
     * @return \Aheadworks\Blog\Api\Data\TagCloudItemSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria, $storeId);
}
