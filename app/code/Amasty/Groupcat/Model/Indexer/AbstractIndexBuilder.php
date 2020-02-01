<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

namespace Amasty\Groupcat\Model\Indexer;

use Amasty\Groupcat\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;

abstract class AbstractIndexBuilder
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var RuleCollectionFactory
     */
    protected $ruleCollectionFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var int
     */
    protected $batchCount;

    /**
     * AbstractIndexBuilder constructor.
     *
     * @param RuleCollectionFactory                     $ruleCollectionFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Psr\Log\LoggerInterface                  $logger
     * @param int                                       $batchCount
     */
    public function __construct(
        RuleCollectionFactory $ruleCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Psr\Log\LoggerInterface $logger,
        $batchCount = 1000
    ) {
        $this->resource              = $resource;
        $this->connection            = $resource->getConnection();
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->logger                = $logger;
        $this->batchCount            = $batchCount;
    }

    /**
     * Reindex by id
     *
     * @param int $id
     *
     * @return void
     * @api
     */
    public function reindexById($id)
    {
        $this->reindexByIds([$id]);
    }

    /**
     * Reindex by ids
     *
     * @param array $ids
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     * @api
     */
    public function reindexByIds(array $ids)
    {
        try {
            $this->doReindexByIds($ids);
        } catch (\Exception $e) {
            $this->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * Reindex by ids. Template method
     *
     * @param array $ids
     *
     * @return void
     */
    abstract protected function doReindexByIds($ids);

    /**
     * Full reindex
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     * @api
     */
    public function reindexFull()
    {
        try {
            $this->doReindexFull();
        } catch (\Exception $e) {
            $this->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * Full reindex Template method
     *
     * @return void
     */
    abstract protected function doReindexFull();

    /**
     * @param string $tableName
     *
     * @return string
     */
    protected function getTable($tableName)
    {
        return $this->resource->getTableName($tableName);
    }

    /**
     * Get active rules
     *
     * @return \Amasty\Groupcat\Model\ResourceModel\Rule\Collection
     */
    protected function getActiveRules()
    {
        return $this->ruleCollectionFactory->create()
            ->addFieldToFilter('is_active', 1);
    }

    /**
     * Get active rules
     *
     * @return \Amasty\Groupcat\Model\ResourceModel\Rule\Collection
     */
    protected function getAllRules()
    {
        return $this->ruleCollectionFactory->create();
    }

    /**
     * @param \Exception $exception
     *
     * @return void
     */
    protected function critical($exception)
    {
        $this->logger->critical($exception);
    }
}
