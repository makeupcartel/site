<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Indexer\RelevanceRule;

use Amasty\ElasticSearch\Api\Data\RelevanceRuleInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexBuilder
{
    const SECONDS_IN_DAY = 86400;
    const PRODUCT_ID = 'product_id';
    const TABLE_NAME = 'amasty_elastic_relevance_rule_index';
    const MAX_INT_MYSQL = 4294967294;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Amasty\ElasticSearch\Api\RelevanceRuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $batchCount;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\ElasticSearch\Api\RelevanceRuleRepositoryInterface $ruleRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Psr\Log\LoggerInterface $logger,
        $batchCount = 1000
    ) {
        $this->resource = $resource;
        $this->storeManager = $storeManager;
        $this->ruleRepository = $ruleRepository;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
        $this->batchCount = $batchCount;
    }

    /**
     * @param array $ids
     */
    public function reindexByIds(array $ids)
    {
        try {
            $this->cleanByIds($ids);
            $products = $this->getProducts($ids);
            foreach ($this->getActiveRules() as $rule) {
                foreach ($products as $product) {
                    $this->applyRule($rule, $product);
                }
            }
        } catch (\Exception $e) {
            $this->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(
                __("Amasty ElasticSearch Relevance rule indexing failed. See details in exception log.")
            );
        }
    }

    /**
     * @param array $productIds
     * @return ProductInterface[]
     */
    public function getProducts(array $productIds)
    {
        $this->searchCriteriaBuilder->addFilter('entity_id', $productIds, 'in');
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $products = $this->productRepository->getList($searchCriteria)->getItems();
        return $products;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function reindexFull()
    {
        try {
            $this->resource->getConnection()->truncateTable($this->getIndexTable());
            foreach ($this->getActiveRules() as $rule) {
                $this->doReindex($rule);
            }
        } catch (\Exception $e) {
            $this->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(
                __("Relevance rule indexing failed. See details in exception log.")
            );
        }
    }

    /**
     * @param array $ids
     */
    public function reindexByRuleIds(array $ids)
    {
        $table = $this->getIndexTable();
        $this->resource->getConnection()->delete(
            $table,
            [
                $this->resource->getConnection()->quoteInto(RelevanceRuleInterface::RULE_ID . ' IN(?)', $ids)
            ]
        );

        try {
            foreach ($ids as $id) {
                try {
                    $rule = $this->ruleRepository->get($id);
                    $this->doReindex($rule);
                } catch (NoSuchEntityException $e) {
                    // do nothing
                }
            }
        } catch (\Exception $e) {
            $this->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(
                __("Relevance rule indexing failed. See details in exception log.")
            );
        }

    }

    /**
     * @param RelevanceRuleInterface $rule
     * @param string $indexTable
     */
    private function doReindex(RelevanceRuleInterface $rule)
    {
        if ($rule->isConditionEmpty()) {
            return;
        }

        $rows = [];
        $size = 0;
        $productIds = $rule->getCatalogRule()->getMatchingProductIds();
        foreach ($productIds as $productId => $validationByWebsite) {
            if (empty($validationByWebsite[$rule->getWebsiteId()])) {
                continue;
            }

            $rows[] = $this->generateIndexData($rule, $productId);
            $size++;
            if ($size == $this->batchCount) {
                $this->resource->getConnection()->insertMultiple($this->getIndexTable(), $rows);
                $rows = [];
                $size = 0;
            }
        }

        if (!empty($rows)) {
            $this->resource->getConnection()->insertMultiple($this->getIndexTable(), $rows);
        }
    }

    /**
     * @param RelevanceRuleInterface $rule
     * @param ProductInterface $product
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function applyRule(RelevanceRuleInterface $rule, ProductInterface $product)
    {
        $table = $this->getIndexTable();
        $this->resource->getConnection()->delete(
            $table,
            [
                $this->resource->getConnection()->quoteInto(RelevanceRuleInterface::RULE_ID . ' = ?', $rule->getId()),
                $this->resource->getConnection()->quoteInto(self::PRODUCT_ID . ' = ?', $product->getId())
            ]
        );

        if (!$rule->getCatalogRule()->validate($product) || $rule->isConditionEmpty()) {
            return $this;
        }

        try {
            $rows = [$this->generateIndexData($rule, $product->getId())];
            $this->resource->getConnection()->insertMultiple($table, $rows);
        } catch (\Exception $e) {
            throw $e;
        }

        return $this;
    }

    /**
     * @param RelevanceRuleInterface $rule
     * @param int $productId
     * @return array
     */
    private function generateIndexData(RelevanceRuleInterface $rule, $productId)
    {
        $toTime = strtotime($rule->getToDate());
        $toTime = $toTime ? $toTime + self::SECONDS_IN_DAY - 1 : self::MAX_INT_MYSQL;

        $row = [
            RelevanceRuleInterface::RULE_ID => $rule->getId(),
            RelevanceRuleInterface::WEBSITE_ID => $rule->getWebsiteId(),
            RelevanceRuleInterface::FROM_DATE =>  strtotime($rule->getFromDate()),
            RelevanceRuleInterface::TO_DATE => $toTime,
            RelevanceRuleInterface::MULTIPLIER => $rule->getMultiplier(),
            self::PRODUCT_ID => $productId
        ];

        return $row;
    }

    /**
     * @param array $productIds
     * @return void
     */
    private function cleanByIds($productIds)
    {
        $query = $this->resource->getConnection()->deleteFromSelect(
            $this->resource->getConnection()
                ->select()
                ->from($this->getIndexTable(), self::PRODUCT_ID)
                ->distinct()
                ->where(self::PRODUCT_ID . ' IN (?)', $productIds),
            $this->getIndexTable()
        );

        $this->resource->getConnection()->query($query);
    }

    /**
     * @return string
     */
    private function getIndexTable()
    {
        return $this->resource->getTableName(self::TABLE_NAME);
    }

    /**
     * @return \Amasty\ElasticSearch\Model\ResourceModel\RelevanceRule\Collection
     */
    private function getActiveRules()
    {
        return $this->ruleRepository->getActiveRules();
    }

    /**
     * @param \Exception $exception
     * @return void
     */
    private function critical(\Exception $exception)
    {
        $this->logger->critical($exception);
    }
}
