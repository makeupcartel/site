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
interface SocialRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\SocialLogin\Api\Data\SocialInterface $social
     * @return \Amasty\SocialLogin\Api\Data\SocialInterface
     */
    public function save(\Amasty\SocialLogin\Api\Data\SocialInterface $social);

    /**
     * Get by id
     *
     * @param int $id
     * @return \Amasty\SocialLogin\Api\Data\SocialInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * Delete
     *
     * @param \Amasty\SocialLogin\Api\Data\SocialInterface $social
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\SocialLogin\Api\Data\SocialInterface $social);

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

    /**
     * Create new item
     *
     * @param $user
     * @param $customerId
     * @param $type
     */
    public function createSocialAccount($user, $customerId, $type);
}
