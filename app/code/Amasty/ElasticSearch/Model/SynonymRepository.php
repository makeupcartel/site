<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model;

use Amasty\ElasticSearch\Api\Data\SynonymInterface;
use Amasty\ElasticSearch\Api\SynonymRepositoryInterface;
use Amasty\ElasticSearch\Model\SynonymFactory;
use Amasty\ElasticSearch\Model\ResourceModel\Synonym as SynonymResource;
use Amasty\ElasticSearch\Model\ResourceModel\Synonym\CollectionFactory;
use Amasty\ElasticSearch\Model\ResourceModel\Synonym\Collection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SynonymRepository implements SynonymRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var SynonymFactory
     */
    private $synonymFactory;

    /**
     * @var SynonymResource
     */
    private $synonymResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $synonyms;

    /**
     * @var CollectionFactory
     */
    private $synonymCollectionFactory;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        SynonymFactory $synonymFactory,
        SynonymResource $synonymResource,
        CollectionFactory $synonymCollectionFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->synonymFactory = $synonymFactory;
        $this->synonymResource = $synonymResource;
        $this->synonymCollectionFactory = $synonymCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(SynonymInterface $synonym)
    {
        try {
            if ($synonym->getSynonymId()) {
                $synonym = $this->getById($synonym->getSynonymId())->addData($synonym->getData());
            }
            $this->synonymResource->save($synonym);
            unset($this->synonyms[$synonym->getSynonymId()]);
        } catch (AlreadyExistsException $e) {
            throw new AlreadyExistsException(__($e->getMessage()));
        } catch (\Exception $e) {
            if ($synonym->getSynonymId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save synonym with ID %1. Error: %2',
                        [$synonym->getSynonymId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new synonym. Error: %1', $e->getMessage()));
        }

        return $synonym;
    }

    /**
     * @inheritdoc
     */
    public function getById($synonymId)
    {
        if (!isset($this->synonyms[$synonymId])) {
            /** @var \Amasty\ElasticSearch\Model\Synonym $synonym */
            $synonym = $this->synonymFactory->create();
            $this->synonymResource->load($synonym, $synonymId);
            if (!$synonym->getSynonymId()) {
                throw new NoSuchEntityException(__('Synonym with specified ID "%1" not found.', $synonymId));
            }
            $this->synonyms[$synonymId] = $synonym;
        }

        return $this->synonyms[$synonymId];
    }

    /**
     * @inheritdoc
     */
    public function delete(SynonymInterface $synonym)
    {
        try {
            $this->synonymResource->delete($synonym);
            unset($this->synonyms[$synonym->getSynonymId()]);
        } catch (\Exception $e) {
            if ($synonym->getSynonymId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove synonym with ID %1. Error: %2',
                        [$synonym->getSynonymId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove synonym. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($synonymId)
    {
        $synonymModel = $this->getById($synonymId);
        $this->delete($synonymModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\ElasticSearch\Model\ResourceModel\Synonym\Collection $synonymCollection */
        $synonymCollection = $this->synonymCollectionFactory->create();
        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $synonymCollection);
        }
        $searchResults->setTotalCount($synonymCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $synonymCollection);
        }
        $synonymCollection->setCurPage($searchCriteria->getCurrentPage());
        $synonymCollection->setPageSize($searchCriteria->getPageSize());
        $synonyms = [];
        /** @var SynonymInterface $synonym */
        foreach ($synonymCollection->getItems() as $synonym) {
            $synonyms[] = $this->getById($synonym->getId());
        }
        $searchResults->setItems($synonyms);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection  $synonymCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $synonymCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $synonymCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection  $synonymCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $synonymCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $synonymCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? 'DESC' : 'ASC'
            );
        }
    }

    /**
     * @param string $query
     * @param int $storeId
     * @return array
     */
    public function getSynonymsByQuery($query, $storeId)
    {
        $result = [];
        if (!$query) {
            return $result;
        }

        $queryArray = explode(' ', $query);
        $queryArray = array_map('trim', $queryArray);
        foreach ($queryArray as &$item) {
            $item = str_replace(',', '', $item);
        }

        $queryArray = array_filter($queryArray);
        if (!$queryArray) {
            return $result;
        }

        $searchString = $this->generateSearchPattern($queryArray);
        $terms = $this->getArrayTermsByConditions($searchString, $storeId);
        if (!$terms) {
            return $result;
        }

        foreach ($queryArray as $word) {
            $search = $this->generateSearchPattern([$word]);
            $search = '/' . $search . '/i';
            $resultWord = [$word];
            foreach ($terms as $term) {
                if (preg_match($search, $term)) {
                    $term = explode(',', $term);
                    $term = array_map('trim', $term);
                    $term = array_filter($term);
                    $resultWord = array_merge($resultWord, $term);
                }
            }
            $result[] = array_unique($resultWord);
        }

        return $result;
    }

    /**
     * @param array $queryArray
     * @return string
     */
    private function generateSearchPattern(array $queryArray)
    {
        $patterns = [];
        foreach ($queryArray as $word) {
            $patterns[] = '^' . $word . ',';
            $patterns[] = ',' . $word . ',';
            $patterns[] = ',' . $word . '$';
        }

        $pattern = implode('|', $patterns);

        return $pattern;
    }

    /**
     * @param $searchString
     * @param $storeId
     * @return array
     */
    private function getArrayTermsByConditions($searchString, $storeId)
    {
        $terms = [];

        $collection = $this->synonymCollectionFactory->create()
            ->addFieldToFilter(SynonymInterface::STORE_ID, $storeId)
            ->addSearchStringFilter($searchString);
        foreach ($collection as $term) {
            $terms[] = $term->getTerm();
        }

        return $terms;
    }
}
