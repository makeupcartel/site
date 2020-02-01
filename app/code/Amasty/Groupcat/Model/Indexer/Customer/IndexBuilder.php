<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

namespace Amasty\Groupcat\Model\Indexer\Customer;

use Amasty\Groupcat\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Amasty\Groupcat\Model\Rule;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Model\Customer;

class IndexBuilder extends \Amasty\Groupcat\Model\Indexer\AbstractIndexBuilder
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Customer[]
     */
    protected $loadedCustomers;

    /**
     * IndexBuilder constructor.
     * @param RuleCollectionFactory $ruleCollectionFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Customer\Model\CustomerRegistry $customerRepository
     * @param int $batchCount
     */
    public function __construct(
        RuleCollectionFactory $ruleCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\CustomerRegistry $customerRepository,
        $batchCount = 1000
    ) {
        parent::__construct($ruleCollectionFactory, $resource, $logger, $batchCount);
        $this->customerRepository = $customerRepository;
    }

    /**
     * Reindex by id
     *
     * @param int $customerId
     *
     * @return void
     * @api
     */
    public function reindexByCustomerId($customerId)
    {
        $this->reindexByCustomerIds([$customerId]);
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
    public function reindexByCustomerIds(array $ids)
    {
        try {
            $this->doReindexByCustomerIds($ids);
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
    protected function doReindexByCustomerIds($ids)
    {
        $ids = $this->removeNotExisting($ids);
        $this->cleanByCustomerIds($ids);

        foreach ($this->getActiveRules() as $rule) {
            foreach ($ids as $customerId) {
                $this->applyRule($rule, $this->getCustomer($customerId));
            }
        }
    }

    /**
     * @param int[] $ids
     * @return array
     */
    protected function removeNotExisting($ids)
    {
        $existingIdsSql = $this->connection
            ->select()
            ->from($this->resource->getTableName('customer_entity'), 'entity_id')
            ->where('entity_id IN (?)', $ids);

        return $this->connection->fetchCol($existingIdsSql);
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
            $this->updateRuleCustomerData($rule);
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
            $this->updateRuleCustomerData($rule);
        }
    }

    /**
     * Clean by customer ids
     *
     * @param array $customerIds
     *
     * @return void
     */
    protected function cleanByCustomerIds($customerIds)
    {
        $query = $this->connection->deleteFromSelect(
            $this->connection
                ->select()
                ->from($this->resource->getTableName('amasty_groupcat_rule_customer'), 'customer_id')
                ->where('customer_id IN (?)', $customerIds),
            $this->resource->getTableName('amasty_groupcat_rule_customer')
        );
        $this->connection->query($query);
    }

    /**
     * Reindex Rule Data By Customer
     *
     * @param Rule    $rule
     * @param Customer $customer
     *
     * @return $this
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function applyRule(Rule $rule, $customer)
    {
        $ruleId          = $rule->getId();
        $customerEntityId = $customer->getId();
        $customerEntityIds = [0, $customerEntityId]; // 0 - not logged in

        if (!$ruleId || !$customerEntityId || !$rule->validateCustomer($customer)) {
            return $this;
        }

        $this->connection->delete(
            $this->resource->getTableName('amasty_groupcat_rule_customer'),
            [
                $this->connection->quoteInto('rule_id = ?', $ruleId),
                $this->connection->quoteInto('customer_id IN (?)', $customerEntityIds)
            ]
        );

        $rows  = [];
        $groupEnabled = $rule->getCustomerGroupEnabled();
        $customerGroupId = $customer->getGroupId();

        if (!$groupEnabled
            || ($groupEnabled && in_array($customerGroupId, $rule->getCustomerGroupIds()))
        ) {
            $rows[] = [
                'rule_id' => $ruleId,
                'customer_id' => $customerEntityId,
            ];
        }

        if (!$groupEnabled
            || ($groupEnabled && in_array(GroupInterface::NOT_LOGGED_IN_ID, $rule->getCustomerGroupIds())
                && ($customerGroupId != GroupInterface::NOT_LOGGED_IN_ID))
        ) {
            $rows[] = [
                'rule_id'     => $ruleId,
                'customer_id' => 0, // not logged in
            ];
        }
        try {
            if (!empty($rows)) {
                $this->connection->insertMultiple($this->resource->getTableName('amasty_groupcat_rule_customer'), $rows);
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return $this;
    }

    /**
     * Collect customer matches for Rule
     *
     * @param Rule $rule
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function updateRuleCustomerData(Rule $rule)
    {
        $ruleId = $rule->getId();
        $this->connection->delete(
            $this->getTable('amasty_groupcat_rule_customer'),
            $this->connection->quoteInto('rule_id=?', $ruleId)
        );

        if (!$rule->getIsActive()) {
            return $this;
        }

        \Magento\Framework\Profiler::start('__MATCH_CUSTOMERS__');
        $customerIds = $rule->getMatchingCustomerIds();
        \Magento\Framework\Profiler::stop('__MATCH_CUSTOMERS__');
        $customerGroupEnabled = $rule->getCustomerGroupEnabled();
        $customerGroupIds     = $customerGroupEnabled ? $rule->getCustomerGroupIds() : [0];

        $rows  = [];
        $count = 0;

        if (in_array(0, $customerGroupIds)) {
            $rows[] = [
                'rule_id'     => $ruleId,
                'customer_id' => 0, // not logged in
            ];
            $count++;
        }
        foreach (array_keys($customerIds) as $customerId) {
            $rows[] = [
                'rule_id'     => $ruleId,
                'customer_id' => $customerId,
            ];

            if (++$count == $this->batchCount) {
                $this->connection->insertMultiple($this->getTable('amasty_groupcat_rule_customer'), $rows);
                $rows  = [];
                $count = 0;
            }

        }
        if (!empty($rows)) {
            $this->connection->insertMultiple($this->getTable('amasty_groupcat_rule_customer'), $rows);
        }

        return $this;
    }

    /**
     * @param int $customerId
     *
     * @return Customer
     */
    protected function getCustomer($customerId)
    {
        if (!isset($this->loadedCustomers[$customerId])) {
            $this->loadedCustomers[$customerId] = $this->customerRepository->retrieve($customerId);
        }

        return $this->loadedCustomers[$customerId];
    }
}
