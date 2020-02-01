<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Api;

/**
 * @api
 */
interface RuleRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\Reports\Api\Data\RuleInterface $rule
     *
     * @return \Amasty\Reports\Api\Data\RuleInterface
     */
    public function save(\Amasty\Reports\Api\Data\RuleInterface $rule);

    /**
     * Get by id
     *
     * @param int $entityId
     *
     * @return \Amasty\Reports\Api\Data\RuleInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($entityId);

    /**
     * Get empty rule object
     *
     * @return \Amasty\Reports\Api\Data\RuleInterface
     */
    public function getNewRule();

    /**
     * Delete
     *
     * @param \Amasty\Reports\Api\Data\RuleInterface $rule
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Reports\Api\Data\RuleInterface $rule);

    /**
     * Delete by id
     *
     * @param int $entityId
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($entityId);

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param int $status
     * @param int|null $ruleId
     *
     * @return bool
     */
    public function updateStatus($status, $ruleId = null);

    /**
     * @param string $date
     * @param int|null $ruleId
     *
     * @return bool
     */
    public function updateLastUpdated($date, $ruleId = null);

    /**
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPinnedRules();
}
