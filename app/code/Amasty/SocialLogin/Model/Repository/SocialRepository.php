<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Model\Repository;

use Amasty\SocialLogin\Api\Data\SocialInterface;
use Amasty\SocialLogin\Api\SocialRepositoryInterface;
use Amasty\SocialLogin\Model\SocialFactory;
use Amasty\SocialLogin\Model\ResourceModel\Social as SocialResource;
use Amasty\SocialLogin\Model\ResourceModel\Social\CollectionFactory;
use Amasty\SocialLogin\Model\ResourceModel\Social\Collection;
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
class SocialRepository implements SocialRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var SocialFactory
     */
    private $socialFactory;

    /**
     * @var SocialResource
     */
    private $socialResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $socials;

    /**
     * @var CollectionFactory
     */
    private $socialCollectionFactory;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    private $customerDataFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        SocialFactory $socialFactory,
        SocialResource $socialResource,
        CollectionFactory $socialCollectionFactory,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->socialFactory = $socialFactory;
        $this->socialResource = $socialResource;
        $this->socialCollectionFactory = $socialCollectionFactory;
        $this->customerDataFactory = $customerDataFactory;
        $this->storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
        $this->registry = $registry;
    }

    /**
     * @inheritdoc
     */
    public function createSocialAccount($user, $customerId, $type)
    {
        /** @var \Amasty\SocialLogin\Model\Social $social */
        $social = $this->socialFactory->create();
        $social->setData(
            [
                'social_id' => $user['identifier'],
                'name' => $user['firstname'] . ' ' . $user['lastname'],
                'customer_id' => $customerId,
                'type' => $type
            ]
        );

        $this->save($social);
    }

    /**
     * @param $socialId
     * @param $type
     *
     * @return bool|\Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomerBySocial($socialId, $type)
    {
        $customer = false;
        $socialCustomer = $this->socialCollectionFactory->create()
            ->addFieldToFilter('social_id', $socialId)
            ->addFieldToFilter('type', $type)
            ->setPageSize(1)
            ->getFirstItem();

        if ($socialCustomer && $socialCustomer->getId()) {
            $customer = $this->customerRepository->getById($socialCustomer->getCustomerId());
        }

        return $customer;
    }

    /**
     * @inheritdoc
     */
    public function save(SocialInterface $social)
    {
        try {
            if ($social->getId()) {
                $social = $this->getById($social->getId())->addData($social->getData());
            }
            $this->socialResource->save($social);
            unset($this->socials[$social->getId()]);
        } catch (\Exception $e) {
            if ($social->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save social data with ID %1. Error: %2',
                        [$social->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new social sata item. Error: %1', $e->getMessage()));
        }

        return $social;
    }

    /**
     * @inheritdoc
     */
    public function getById($id)
    {
        if (!isset($this->socials[$id])) {
            /** @var \Amasty\SocialLogin\Model\Social $social */
            $social = $this->socialFactory->create();
            $this->socialResource->load($social, $id);
            if (!$social->getId()) {
                throw new NoSuchEntityException(__('Social item with specified ID "%1" not found.', $id));
            }
            $this->socials[$id] = $social;
        }

        return $this->socials[$id];
    }

    /**
     * @inheritdoc
     */
    public function delete(SocialInterface $social)
    {
        try {
            $this->socialResource->delete($social);
            unset($this->socials[$social->getId()]);
        } catch (\Exception $e) {
            if ($social->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove social item with ID %1. Error: %2',
                        [$social->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove social item. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($id)
    {
        $socialModel = $this->getById($id);
        $this->delete($socialModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\SocialLogin\Model\ResourceModel\Social\Collection $socialCollection */
        $socialCollection = $this->socialCollectionFactory->create();
        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $socialCollection);
        }
        $searchResults->setTotalCount($socialCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $socialCollection);
        }
        $socialCollection->setCurPage($searchCriteria->getCurrentPage());
        $socialCollection->setPageSize($searchCriteria->getPageSize());
        $socials = [];
        /** @var SocialInterface $social */
        foreach ($socialCollection->getItems() as $social) {
            $socials[] = $this->getById($social->getId());
        }
        $searchResults->setItems($socials);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection  $socialCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $socialCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $socialCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
    * Helper function that adds a SortOrder to the collection.
    *
    * @param SortOrder[] $sortOrders
    * @param Collection  $socialCollection
    *
    * @return void
    */
    private function addOrderToCollection($sortOrders, Collection $socialCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $socialCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? 'DESC' : 'ASC'
            );
        }
    }

    /**
     * @param $user
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function createCustomer($user)
    {
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $this->customerDataFactory->create();
        $store = $this->storeManager->getStore();

        $lastName = $user['lastname'] ?: $user['firstname'];
        return $customer->setFirstname($user['firstname'])
            ->setLastname($lastName)
            ->setEmail($user['email'])
            ->setStoreId($store->getId())
            ->setWebsiteId($store->getWebsiteId())
            ->setCreatedIn($store->getName());
    }
}
