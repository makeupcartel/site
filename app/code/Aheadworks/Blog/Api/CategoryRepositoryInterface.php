<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Category CRUD interface
 * @api
 */
interface CategoryRepositoryInterface
{
    /**
     * Save category
     *
     * @param \Aheadworks\Blog\Api\Data\CategoryInterface $category
     * @return \Aheadworks\Blog\Api\Data\CategoryInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Aheadworks\Blog\Api\Data\CategoryInterface $category);

    /**
     * Retrieve category
     *
     * @param int $categoryId
     * @return \Aheadworks\Blog\Api\Data\CategoryInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($categoryId);

    /**
     * Retrieve category by url key
     *
     * @param string $categoryUrlKey
     * @return \Aheadworks\Blog\Api\Data\CategoryInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByUrlKey($categoryUrlKey);

    /**
     * Retrieve categories matching the specified criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Aheadworks\Blog\Api\Data\CategorySearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete category
     *
     * @param \Aheadworks\Blog\Api\Data\CategoryInterface $category
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Aheadworks\Blog\Api\Data\CategoryInterface $category);

    /**
     * Delete category by ID
     *
     * @param int $categoryId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($categoryId);
}
