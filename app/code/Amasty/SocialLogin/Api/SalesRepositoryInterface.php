<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Api;

/**
 * @api
 */
interface SalesRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\SocialLogin\Api\Data\SalesInterface $sales
     * @return \Amasty\SocialLogin\Api\Data\SalesInterface
     */
    public function save(\Amasty\SocialLogin\Api\Data\SalesInterface $sales);

    /**
     * Get by id
     *
     * @param int $id
     * @return \Amasty\SocialLogin\Api\Data\SalesInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * Delete
     *
     * @param \Amasty\SocialLogin\Api\Data\SalesInterface $sales
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\SocialLogin\Api\Data\SalesInterface $sales);

    /**
     * Delete by id
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($id);

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
