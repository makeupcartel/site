<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

namespace Amasty\Groupcat\Model\Indexer\Product;

use Magento\Catalog\Model\Product;
use Amasty\Groupcat\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Amasty\Groupcat\Model\Rule;

class IndexBuilder extends \Amasty\Groupcat\Model\Indexer\AbstractIndexBuilder
{
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var Product[]
     */
    protected $loadedProducts;

    /**
     * IndexBuilder constructor.
     *
     * @param RuleCollectionFactory                     $ruleCollectionFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Psr\Log\LoggerInterface                  $logger
     * @param \Magento\Catalog\Model\ProductRepository  $productRepository
     * @param int                                       $batchCount
     */
    public function __construct(
        RuleCollectionFactory $ruleCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        $batchCount = 1000
    ) {
        parent::__construct($ruleCollectionFactory, $resource, $logger, $batchCount);
        $this->productRepository     = $productRepository;
    }

    /**
     * Reindex by id
     *
     * @param int $productId
     *
     * @return void
     * @api
     */
    public function reindexByProductId($productId)
    {
        $this->reindexByProductIds([$productId]);
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
    public function reindexByProductIds(array $ids)
    {
        try {
            $this->doReindexByProductIds($ids);
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
    protected function doReindexByProductIds($ids)
    {
        $this->cleanByProductIds($ids);

        foreach ($this->getActiveRules() as $rule) {
            foreach ($ids as $productId) {
                $this->applyRule($rule, $productId);
            }
        }
    }

    /**
     * Reindex by ids. Template method
     *
     * @param array $ids
     *
     * @return void
     */
    protected function doReindexByIds($ids)
    {
        $collection = $this->getAllRules();
        $collection->addFieldToFilter('rule_id', ['in' => $ids]);

        foreach ($collection as $rule) {
            $this->updateRuleProductData($rule);
        }
    }

    /**
     * Full reindex Template method
     *
     * @return void
     */
    protected function doReindexFull()
    {
        foreach ($this->getAllRules() as $rule) {
            $this->updateRuleProductData($rule);
        }
    }

    /**
     * Clean by product ids
     *
     * @param array $productIds
     *
     * @return void
     */
    protected function cleanByProductIds($productIds)
    {
        $query = $this->connection->deleteFromSelect(
            $this->connection
                ->select()
                ->from($this->resource->getTableName('amasty_groupcat_rule_product'), 'product_id')
                ->distinct()
                ->where('product_id IN (?)', $productIds),
            $this->resource->getTableName('amasty_groupcat_rule_product')
        );
        $this->connection->query($query);
    }

    /**
     * Reindex Rule Data By Product
     *
     * @param Rule    $rule
     * @param int     $productEntityId
     *
     * @return $this
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function applyRule(Rule $rule, $productEntityId)
    {
        $ruleId          = $rule->getId();
        $storeIds        = $rule->getStoreIds();
        $validOnStores   = [];

        foreach ($storeIds as $storeId) {
            try {
                $product = $this->productRepository->getById($productEntityId, false, $storeId);
                if ($rule->validate($product)) {
                    $validOnStores[$storeId] = true;
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->critical($e);
            }
        }
        if (!count($validOnStores)) {
            return $this;
        }

        $this->connection->delete(
            $this->resource->getTableName('amasty_groupcat_rule_product'),
            [
                $this->connection->quoteInto('rule_id = ?', $ruleId),
                $this->connection->quoteInto('product_id = ?', $productEntityId)
            ]
        );

        $fromTime = $toTime = 0;
        if ($rule->getDateRangeEnabled()) {
            $fromTime = strtotime($rule->getFromDate());
            $toTime   = strtotime($rule->getToDate() . ' 23:59:59');
        }
        $customerGroupEnabled = $rule->getCustomerGroupEnabled();
        $customerGroupIds     = $customerGroupEnabled ? $rule->getCustomerGroupIds() : [0];
        $hideProduct          = $rule->getHideProduct();
        $hideCart             = $rule->getHideCart();
        $hideWishlist         = $rule->getHideWishlist();
        $hideCompare          = $rule->getHideCompare();
        $priority             = (int)$rule->getPriority();
        $priceAction          = $rule->getPriceAction();

        $rows  = [];
        $count = 0;
        try {
            /* Note: Rule can be for All Store View (sore_ids = array(0 => '0')) */
            foreach (array_keys($validOnStores) as $storeId) {
                foreach ($customerGroupIds as $customerGroupId) {
                    $rows[] = [
                        'rule_id'                => $ruleId,
                        'from_time'              => $fromTime,
                        'to_time'                => $toTime,
                        'store_id'               => $storeId,
                        'customer_group_enabled' => $customerGroupEnabled,
                        'customer_group_id'      => $customerGroupId,
                        'product_id'             => $productEntityId,
                        'price_action'           => $priceAction,
                        'hide_cart'              => $hideCart,
                        'hide_product'           => $hideProduct,
                        'hide_wishlist'          => $hideWishlist,
                        'hide_compare'           => $hideCompare,
                        'priority'               => $priority,
                    ];

                    if (++$count == $this->batchCount) {
                        $this->connection->insertMultiple($this->getTable('amasty_groupcat_rule_product'), $rows);
                        $rows  = [];
                        $count = 0;
                    }
                }
            }

            if (!empty($rows)) {
                $this->connection->insertMultiple($this->resource->getTableName('amasty_groupcat_rule_product'), $rows);
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return $this;
    }

    /**
     * Collect product matches for Rule
     *
     * @param Rule $rule
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function updateRuleProductData(Rule $rule)
    {
        $ruleId = $rule->getId();
        $this->connection->delete(
            $this->getTable('amasty_groupcat_rule_product'),
            $this->connection->quoteInto('rule_id=?', $ruleId)
        );

        if (!$rule->getIsActive()) {
            return $this;
        }

        $storeIds = $rule->getStoreIds();
        if (!is_array($storeIds)) {
            $storeIds = explode(',', $storeIds);
        }
        if (empty($storeIds)) {
            return $this;
        }

        \Magento\Framework\Profiler::start('__MATCH_PRODUCTS__');
        $productIds = $rule->getMatchingProductIds();
        \Magento\Framework\Profiler::stop('__MATCH_PRODUCTS__');
        $fromTime = $toTime = 0;
        if ($rule->getDateRangeEnabled()) {
            $fromTime = strtotime($rule->getFromDate());
            $toTime   = strtotime($rule->getToDate() . ' 23:59:59');
        }
        $customerGroupEnabled = $rule->getCustomerGroupEnabled();
        $customerGroupIds     = $customerGroupEnabled ? $rule->getCustomerGroupIds() : [0];
        $hideProduct          = $rule->getHideProduct();
        $hideCart             = $rule->getHideCart();
        $hideWishlist         = $rule->getHideWishlist();
        $hideCompare          = $rule->getHideCompare();
        $priority             = (int)$rule->getPriority();
        $priceAction          = $rule->getPriceAction();

        $rows  = [];
        $count = 0;

        foreach ($productIds as $productId => $validationByStore) {
            /* Note: Rule can be for All Store View (rule.sore_ids = array(0 => '0')) */
            foreach ($storeIds as $storeId) {
                if (empty($validationByStore[$storeId])) {
                    continue;
                }
                foreach ($customerGroupIds as $customerGroupId) {
                    $rows[] = [
                        'rule_id'                => $ruleId,
                        'from_time'              => $fromTime,
                        'to_time'                => $toTime,
                        'store_id'               => $storeId,
                        'customer_group_enabled' => $customerGroupEnabled,
                        'customer_group_id'      => $customerGroupId,
                        'product_id'             => $productId,
                        'price_action'           => $priceAction,
                        'hide_cart'              => $hideCart,
                        'hide_product'           => $hideProduct,
                        'hide_wishlist'          => $hideWishlist,
                        'hide_compare'           => $hideCompare,
                        'priority'               => $priority,
                    ];

                    if (++$count == $this->batchCount) {
                        $this->connection->insertMultiple($this->getTable('amasty_groupcat_rule_product'), $rows);
                        $rows  = [];
                        $count = 0;
                    }
                }
            }
        }
        if (!empty($rows)) {
            $this->connection->insertMultiple($this->getTable('amasty_groupcat_rule_product'), $rows);
        }

        return $this;
    }
}
