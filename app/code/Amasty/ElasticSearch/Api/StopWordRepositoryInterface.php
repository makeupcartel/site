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
interface StopWordRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\ElasticSearch\Api\Data\StopWordInterface $stopWord
     * @return \Amasty\ElasticSearch\Api\Data\StopWordInterface
     */
    public function save(\Amasty\ElasticSearch\Api\Data\StopWordInterface $stopWord);

    /**
     * Get by id
     *
     * @param int $stopWordId
     * @return \Amasty\ElasticSearch\Api\Data\StopWordInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($stopWordId);

    /**
     * Delete
     *
     * @param \Amasty\ElasticSearch\Api\Data\StopWordInterface $stopWord
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\ElasticSearch\Api\Data\StopWordInterface $stopWord);

    /**
     * Delete by id
     *
     * @param int $stopWordId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($stopWordId);

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Stop Words array by storeid
     *
     * @param int $storeId
     * @return array
     */
    public function getArrayListByStoreId($storeId);

    /**
     * @param $file
     * @param $storeId
     * @return int
     * @throws \Exception
     */
    public function importStopWords($file, $storeId);
}
