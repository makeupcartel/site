<?php

namespace Unirgy\RapidFlow\Model\ResourceModel;

use Magento\CatalogRule\Model\ResourceModel\Rule;
use Magento\CatalogRule\Model\Rule as ModelRule;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Profiler;
use Magento\Framework\Stdlib\DateTime as StdlibDateTime;
use Magento\Store\Model\Group;
use Unirgy\RapidFlow\Helper\Data as RfData;
use Unirgy\RapidFlow\Model\ResourceModel\Catalog\Product as RfProduct;

class CatalogRule extends Rule
{
    /**
     * @var RfData
     */
    private $rfHelper;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\ConditionFactory $conditionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\CatalogRule\Helper\Data $catalogRuleData,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        PriceCurrencyInterface $priceCurrency,
        RfData $dataHelper,
        $connectionName = null
    ) {
        parent::__construct($context, $storeManager, $conditionFactory, $coreDate, $eavConfig, $eventManager,
                            $catalogRuleData, $logger, $dateTime, $priceCurrency, $connectionName);
        $this->rfHelper = $dataHelper;
    }

    /**
     * Remove catalog rules product prices for specified date range and product
     *
     * @param   int|string $fromDate
     * @param   int|string $toDate
     * @param   int|null $productId
     * @return  Rule
     */
    public function removeCatalogPricesForDateRange($fromDate, $toDate, $productId = null)
    {
        $write = $this->getConnection();
        $conds = [];
        $cond = $write->quoteInto('rule_date between ?', $this->dateTime->formatDate($fromDate));
        $cond = $write->quoteInto($cond . ' and ?', $this->dateTime->formatDate($toDate));
        $conds[] = $cond;
        if (!is_null($productId)) {
            $conds[] = $write->quoteInto('product_id in (?)', $productId);
        }

        /**
         * Add information about affected products
         * It can be used in processes which related with product price (like catalog index)
         */
//        $select = $this->getConnection()->select()
//            ->from($this->getTable('catalogrule_product_price'), 'product_id')
//            ->where(implode(' AND ', $conds));
//        $insertQuery = 'REPLACE INTO ' . $this->getTable('catalogrule/affected_product') . ' (product_id)' . $select->__toString();
//        $this->getConnection()->query($insertQuery);
        $write->delete($this->getTable('catalogrule_product_price'), $conds);
        return $this;
    }

    /**
     * Delete old price rules data
     *
     * @param $date
     * @param   mixed $productId
     * @return Rule
     */
    public function deleteOldData($date, $productId = null)
    {
        $write = $this->getConnection();
        $conds = [];
        $conds[] = $write->quoteInto('rule_date<?', $this->dateTime->formatDate($date));
        if (!is_null($productId)) {
            $conds[] = $write->quoteInto('product_id in (?)', $productId);
        }
        $write->delete($this->getTable('catalogrule_product_price'), $conds);
        return $this;
    }

    /**
     * Get DB resource statment for processing query result
     *
     * @param   int $fromDate
     * @param   int $toDate
     * @param   int|null $productId
     * @param   int|null $websiteId
     * @return \Zend_Db_Statement_Interface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getRuleProductsStmt($fromDate, $toDate, $productId = null, $websiteId = null)
    {
        $read = $this->getConnection();
        /**
         * Sort order is important
         * It used for check stop price rule condition.
         * website_id   customer_group_id   product_id  sort_order
         *  1           1                   1           0
         *  1           1                   1           1
         *  1           1                   1           2
         * if row with sort order 1 will have stop flag we should exclude
         * all next rows for same product id from price calculation
         */
        $select = $read->select()
            ->from(['rp' => $this->getTable('catalogrule_product')])
            ->where($read->quoteInto('rp.from_time=0 or rp.from_time<=?', $toDate) . ' or ' . $read->quoteInto('rp.to_time=0 or rp.to_time>=?', $fromDate))
            ->order(['rp.website_id', 'rp.customer_group_id', 'rp.product_id', 'rp.sort_order', 'rp.rule_id']);

        if (null !== $productId) {
            $select->where('rp.product_id in (?)', $productId);
        }

        /**
         * Join default price and websites prices to result
         */
        $priceAttr = $this->_eavConfig->getAttribute('catalog_product', 'price');
        $priceTable = $priceAttr->getBackend()->getTable();
        $attributeId = $priceAttr->getId();
        $entityId = 'entity_id';
        if ($this->rfHelper->hasMageFeature(RfProduct::ROW_ID)) {
            $entityId = RfProduct::ROW_ID;
        }
        $joinCondition = '%1$s.' . $entityId . '=rp.product_id AND (%1$s.attribute_id=' . $attributeId . ') and %1$s.store_id=%2$s';

        $defaultStoreId = $this->_storeManager->getDefaultStoreView()->getId();
        $select->join(
            ['pp_default' => $priceTable],
            sprintf($joinCondition, 'pp_default', $defaultStoreId),
            ['default_price' => 'pp_default.value']
        );

        if ($websiteId !== null) {
            $website = $this->_storeManager->getWebsite($websiteId);
            $defaultGroupId = $website->getDefaultGroupId();
            $defaultGroup = $this->_storeManager->getGroup($defaultGroupId);
            if ($defaultGroup instanceof Group) {
                $storeId = $defaultGroup->getDefaultStoreId();
            } else {
                $storeId = $defaultStoreId;
            }

            $select->joinInner(
                array('product_website' => $this->getTable('catalog_product_website')),
                'product_website.product_id=rp.product_id AND rp.website_id=product_website.website_id AND product_website.website_id=' . $websiteId,
                []
            );

            $tableAlias = 'pp' . $websiteId;
            $fieldAlias = 'website_' . $websiteId . '_price';
            $select->joinLeft(
                array($tableAlias => $priceTable),
                sprintf($joinCondition, $tableAlias, $storeId),
                array($fieldAlias => $tableAlias . '.value')
            );
        } else {
            foreach ($this->_storeManager->getWebsites() as $website) {
                $websiteId = $website->getId();
                $defaultGroupId = $website->getDefaultGroupId();
                $defaultGroup = $this->_storeManager->getGroup($defaultGroupId);
                if ($defaultGroup instanceof Group) {
                    $storeId = $defaultGroup->getDefaultStoreId();
                } else {
                    $storeId = $defaultStoreId;
                }

//                $storeId = $defaultGroup->getDefaultStoreId();
                $tableAlias = 'pp' . $websiteId;
                $fieldAlias = 'website_' . $websiteId . '_price';
                $select->joinLeft(
                    array($tableAlias => $priceTable),
                    sprintf($joinCondition, $tableAlias, $storeId),
                    array($fieldAlias => $tableAlias . '.value')
                );
            }
        }
        return $read->query($select);
    }

    public function updateRuleMultiProductData(ModelRule $rule, $pIds)
    {
        $ruleId = $rule->getId();
        $write = $this->getConnection();
        $write->beginTransaction();

        $write->delete($this->getTable('catalogrule_product'), $write->quoteInto('rule_id=?', $ruleId));

        if (!$rule->getIsActive()) {
            $write->commit();
            return $this;
        }

        $websiteIds = $rule->getWebsiteIds();
        if (empty($websiteIds)) {
            $write->commit();
            return $this;
        }
        if (!is_array($websiteIds)) {
            $websiteIds = explode(',', $websiteIds);
        }
        Profiler::start('__MATCH_PRODUCTS__');
        $productIds = $rule->getMatchingMultiProductIds($pIds);
        Profiler::stop('__MATCH_PRODUCTS__');
        $customerGroupIds = $rule->getCustomerGroupIds();

        $fromTime = strtotime($rule->getFromDate());
        $toTime = strtotime($rule->getToDate());
        $toTime = $toTime ? ($toTime + self::SECONDS_IN_DAY - 1) : 0;

        $sortOrder = (int)$rule->getSortOrder();
        $actionOperator = $rule->getSimpleAction();
        $actionAmount = $rule->getDiscountAmount();
        $actionStop = $rule->getStopRulesProcessing();

        $rows = [];
        $queryStart = 'INSERT INTO ' . $this->getTable('catalogrule_product') . ' (
                rule_id, from_time, to_time, website_id, customer_group_id, product_id, action_operator,
                action_amount, action_stop, sort_order ) VALUES ';
        $queryEnd = ' ON DUPLICATE KEY UPDATE action_operator=VALUES(action_operator),
            action_amount=VALUES(action_amount), action_stop=VALUES(action_stop)';
        try {
            foreach ($productIds as $productId) {
                foreach ($websiteIds as $websiteId) {
                    foreach ($customerGroupIds as $customerGroupId) {
                        $rows[] = "('" . implode("','", array(
                                $ruleId,
                                $fromTime,
                                $toTime,
                                $websiteId,
                                $customerGroupId,
                                $productId,
                                $actionOperator,
                                $actionAmount,
                                $actionStop,
                                $sortOrder
                            )) . "')";
                        /**
                         * Array with 1000 rows contain about 2M data
                         */
                        if (count($rows) === 1000) {
                            $sql = $queryStart . implode(',', $rows) . $queryEnd;
                            $write->query($sql);
                            $rows = [];
                        }
                    }
                }
            }
            if (!empty($rows)) {
                $sql = $queryStart . implode(',', $rows) . $queryEnd;
                $write->query($sql);
            }

            $write->commit();
        } catch (\Exception $e) {
            $write->rollBack();
            throw $e;
        }

        return $this;
    }
}
