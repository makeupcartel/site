<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Api\Repository;

/**
 * Interface WhitelistRepositoryInterface
 *
 * @package BitExpert\ForceCustomerLogin\Api\Repository
 */
interface WhitelistRepositoryInterface
{
    /*
     * Special store ids
     */
    const ROOT_STORE_ID = 0;
    /*
     * Strategy
     */
    const DEFAULT_STRATEGY = 'default';

    /**
     * Get collection {@link \BitExpert\ForceCustomerLogin\Model\ResourceModel\WhitelistEntry\Collection}.
     *
     * @return \BitExpert\ForceCustomerLogin\Model\ResourceModel\WhitelistEntry\Collection
     */
    public function getCollection();

    /**
     * Search by criterias for whitelist entries.
     *
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria
     * @return \BitExpert\ForceCustomerLogin\Api\Data\Collection\WhitelistEntrySearchResultInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria);

    /**
     * @param int|null $entityId If NULL a new entity will be created
     * @param string $label
     * @param string $urlRule
     * @param string $strategy
     * @param int $storeId
     * @return \BitExpert\ForceCustomerLogin\Model\WhitelistEntry
     */
    public function createEntry($entityId, $label, $urlRule, $strategy = self::DEFAULT_STRATEGY, $storeId = 0);

    /**
     * @param int $id
     * @return boolean
     */
    public function deleteEntry($id);
}
