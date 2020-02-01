<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Api;

/**
 * @api
 */
interface SynonymRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\ElasticSearch\Api\Data\SynonymInterface $synonym
     * @return \Amasty\ElasticSearch\Api\Data\SynonymInterface
     */
    public function save(\Amasty\ElasticSearch\Api\Data\SynonymInterface $synonym);

    /**
     * Get by id
     *
     * @param int $synonymId
     * @return \Amasty\ElasticSearch\Api\Data\SynonymInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($synonymId);

    /**
     * Delete
     *
     * @param \Amasty\ElasticSearch\Api\Data\SynonymInterface $synonym
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\ElasticSearch\Api\Data\SynonymInterface $synonym);

    /**
     * Delete by id
     *
     * @param int $synonymId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($synonymId);

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
