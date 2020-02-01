<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Model\Repository;

use Amasty\SocialLogin\Api\Data\SalesInterface;
use Amasty\SocialLogin\Api\SalesRepositoryInterface;
use Amasty\SocialLogin\Model\SalesFactory;
use Amasty\SocialLogin\Model\ResourceModel\Sales as SalesResource;
use Amasty\SocialLogin\Model\ResourceModel\Sales\CollectionFactory;
use Amasty\SocialLogin\Model\ResourceModel\Sales\Collection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SalesRepository implements SalesRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var SalesFactory
     */
    private $salesFactory;

    /**
     * @var SalesResource
     */
    private $salesResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $saless;

    /**
     * @var CollectionFactory
     */
    private $salesCollectionFactory;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        SalesFactory $salesFactory,
        SalesResource $salesResource,
        CollectionFactory $salesCollectionFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->salesFactory = $salesFactory;
        $this->salesResource = $salesResource;
        $this->salesCollectionFactory = $salesCollectionFactory;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @inheritdoc
     */
    public function createItem(\Magento\Sales\Model\Order $order, $userProfile)
    {
        /** @var \Amasty\SocialLogin\Model\Sales $sales */
        $sales = $this->salesFactory->create();

        $total = $this->priceCurrency->convert(
            $order->getBaseGrandTotal(),
            $order->getStore(),
            $order->getBaseCurrency()
        );

        $sales->setSocialId($userProfile)
            ->setAmount($total)
            ->setItems($order->getTotalQtyOrdered());

        $this->save($sales);
    }

    /**
     * @inheritdoc
     */
    public function save(SalesInterface $sales)
    {
        try {
            if ($sales->getId()) {
                $sales = $this->getById($sales->getId())->addData($sales->getData());
            }
            $this->salesResource->save($sales);
            unset($this->saless[$sales->getId()]);
        } catch (\Exception $e) {
            if ($sales->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save sales with ID %1. Error: %2',
                        [$sales->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new sales. Error: %1', $e->getMessage()));
        }

        return $sales;
    }

    /**
     * @inheritdoc
     */
    public function getById($id)
    {
        if (!isset($this->saless[$id])) {
            /** @var \Amasty\SocialLogin\Model\Sales $sales */
            $sales = $this->salesFactory->create();
            $this->salesResource->load($sales, $id);
            if (!$sales->getId()) {
                throw new NoSuchEntityException(__('Sales with specified ID "%1" not found.', $id));
            }
            $this->saless[$id] = $sales;
        }

        return $this->saless[$id];
    }

    /**
     * @inheritdoc
     */
    public function delete(SalesInterface $sales)
    {
        try {
            $this->salesResource->delete($sales);
            unset($this->saless[$sales->getId()]);
        } catch (\Exception $e) {
            if ($sales->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove sales with ID %1. Error: %2',
                        [$sales->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove sales. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($id)
    {
        $salesModel = $this->getById($id);
        $this->delete($salesModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\SocialLogin\Model\ResourceModel\Sales\Collection $salesCollection */
        $salesCollection = $this->salesCollectionFactory->create();
        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $salesCollection);
        }
        $searchResults->setTotalCount($salesCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $salesCollection);
        }
        $salesCollection->setCurPage($searchCriteria->getCurrentPage());
        $salesCollection->setPageSize($searchCriteria->getPageSize());
        $saless = [];
        /** @var SalesInterface $sales */
        foreach ($salesCollection->getItems() as $sales) {
            $saless[] = $this->getById($sales->getId());
        }
        $searchResults->setItems($saless);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection  $salesCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $salesCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $salesCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
    * Helper function that adds a SortOrder to the collection.
    *
    * @param SortOrder[] $sortOrders
    * @param Collection  $salesCollection
    *
    * @return void
    */
    private function addOrderToCollection($sortOrders, Collection $salesCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $salesCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? 'DESC' : 'ASC'
            );
        }
    }
}
