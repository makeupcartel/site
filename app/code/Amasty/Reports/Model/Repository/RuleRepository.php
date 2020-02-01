<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\Repository;

use Amasty\Reports\Api\Data\RuleInterface;
use Amasty\Reports\Api\RuleRepositoryInterface;
use Amasty\Reports\Model\RuleFactory;
use Amasty\Reports\Model\ResourceModel\Rule as RuleResource;
use Amasty\Reports\Model\ResourceModel\Rule\CollectionFactory;
use Amasty\Reports\Model\ResourceModel\Rule\Collection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RuleRepository implements RuleRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var RuleResource
     */
    private $ruleResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $rules = [];

    /**
     * @var CollectionFactory
     */
    private $ruleCollectionFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        RuleFactory $ruleFactory,
        RuleResource $ruleResource,
        CollectionFactory $ruleCollectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->ruleFactory = $ruleFactory;
        $this->ruleResource = $ruleResource;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritdoc
     */
    public function save(RuleInterface $rule)
    {
        try {
            if ($rule->getEntityId()) {
                $rule = $this->getById($rule->getEntityId())->addData($rule->getData());
            }
            $this->ruleResource->save($rule);
            unset($this->rules[$rule->getEntityId()]);
        } catch (\Exception $e) {
            if ($rule->getEntityId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save rule with ID %1. Error: %2',
                        [$rule->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new rule. Error: %1', $e->getMessage()));
        }

        return $rule;
    }

    /**
     * @inheritdoc
     */
    public function getById($entityId)
    {
        if (!isset($this->rules[$entityId])) {
            /** @var \Amasty\Reports\Model\Rule $rule */
            $rule = $this->ruleFactory->create();
            $this->ruleResource->load($rule, $entityId);
            if (!$rule->getEntityId()) {
                throw new NoSuchEntityException(__('Rule with specified ID "%1" not found.', $entityId));
            }
            $this->rules[$entityId] = $rule;
        }

        return $this->rules[$entityId];
    }

    /**
     * @inheritdoc
     */
    public function getNewRule()
    {
        return $this->ruleFactory->create();
    }

    /**
     * @inheritdoc
     */
    public function delete(RuleInterface $rule)
    {
        try {
            $this->ruleResource->delete($rule);
            unset($this->rules[$rule->getEntityId()]);
        } catch (\Exception $e) {
            if ($rule->getEntityId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove rule with ID %1. Error: %2',
                        [$rule->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove rule. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($entityId)
    {
        $ruleModel = $this->getById($entityId);
        $this->delete($ruleModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\Reports\Model\ResourceModel\Rule\Collection $ruleCollection */
        $ruleCollection = $this->ruleCollectionFactory->create();
        
        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $ruleCollection);
        }
        
        $searchResults->setTotalCount($ruleCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        
        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $ruleCollection);
        }
        
        $ruleCollection->setCurPage($searchCriteria->getCurrentPage());
        $ruleCollection->setPageSize($searchCriteria->getPageSize());
        
        $rules = [];
        /** @var RuleInterface $rule */
        foreach ($ruleCollection->getItems() as $rule) {
            $rules[] = $this->getById($rule->getEntityId());
        }
        
        $searchResults->setItems($rules);

        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function updateStatus($status, $ruleId = null)
    {
        $this->ruleResource->updateStatus($status, $ruleId);
        return true;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection  $ruleCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $ruleCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $ruleCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection  $ruleCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $ruleCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $ruleCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? SortOrder::SORT_DESC : SortOrder::SORT_ASC
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function updateLastUpdated($date, $ruleId = null)
    {
        $this->ruleResource->updateLastUpdated($date, $ruleId);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getPinnedRules()
    {
        $this->searchCriteriaBuilder->addFilter(RuleInterface::PIN, 1);
        return $this->getList($this->searchCriteriaBuilder->create());
    }
}
