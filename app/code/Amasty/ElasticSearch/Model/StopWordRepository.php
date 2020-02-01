<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model;

use Amasty\ElasticSearch\Api\Data\StopWordInterface;
use Amasty\ElasticSearch\Api\StopWordRepositoryInterface;
use Amasty\ElasticSearch\Model\ResourceModel\StopWord as StopWordResource;
use Amasty\ElasticSearch\Model\ResourceModel\StopWord\CollectionFactory;
use Amasty\ElasticSearch\Model\ResourceModel\StopWord\Collection;
use Magento\Framework\Exception\AlreadyExistsException;
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
class StopWordRepository implements StopWordRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var StopWordFactory
     */
    private $stopWordFactory;

    /**
     * @var StopWordResource
     */
    private $stopWordResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $stopWords;

    /**
     * @var CollectionFactory
     */
    private $stopWordCollectionFactory;

    /**
     * @var \Magento\Framework\File\Csv
     */
    private $csv;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        StopWordFactory $stopWordFactory,
        StopWordResource $stopWordResource,
        CollectionFactory $stopWordCollectionFactory,
        \Magento\Framework\File\Csv $csv
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->stopWordFactory = $stopWordFactory;
        $this->stopWordResource = $stopWordResource;
        $this->stopWordCollectionFactory = $stopWordCollectionFactory;
        $this->csv = $csv;
    }

    /**
     * @inheritdoc
     */
    public function save(StopWordInterface $stopWord)
    {
        try {
            if ($stopWord->getStopWordId()) {
                $stopWord = $this->getById($stopWord->getStopWordId())->addData($stopWord->getData());
            }
            $this->stopWordResource->save($stopWord);
            unset($this->stopWords[$stopWord->getStopWordId()]);
        } catch (AlreadyExistsException $e) {
            throw new AlreadyExistsException(__($e->getMessage()));
        } catch (\Exception $e) {
            if ($stopWord->getStopWordId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save stopWord with ID %1. Error: %2',
                        [$stopWord->getStopWordId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new stopWord. Error: %1', $e->getMessage()));
        }

        return $stopWord;
    }

    /**
     * @inheritdoc
     */
    public function getById($stopWordId)
    {
        if (!isset($this->stopWords[$stopWordId])) {
            /** @var \Amasty\ElasticSearch\Model\StopWord $stopWord */
            $stopWord = $this->stopWordFactory->create();
            $this->stopWordResource->load($stopWord, $stopWordId);
            if (!$stopWord->getStopWordId()) {
                throw new NoSuchEntityException(__('StopWord with specified ID "%1" not found.', $stopWordId));
            }
            $this->stopWords[$stopWordId] = $stopWord;
        }

        return $this->stopWords[$stopWordId];
    }

    /**
     * @inheritdoc
     */
    public function delete(StopWordInterface $stopWord)
    {
        try {
            $this->stopWordResource->delete($stopWord);
            unset($this->stopWords[$stopWord->getStopWordId()]);
        } catch (\Exception $e) {
            if ($stopWord->getStopWordId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove stopWord with ID %1. Error: %2',
                        [$stopWord->getStopWordId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove stopWord. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($stopWordId)
    {
        $stopWordModel = $this->getById($stopWordId);
        $this->delete($stopWordModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\ElasticSearch\Model\ResourceModel\StopWord\Collection $stopWordCollection */
        $stopWordCollection = $this->stopWordCollectionFactory->create();
        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $stopWordCollection);
        }
        $searchResults->setTotalCount($stopWordCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $stopWordCollection);
        }
        $stopWordCollection->setCurPage($searchCriteria->getCurrentPage());
        $stopWordCollection->setPageSize($searchCriteria->getPageSize());
        $stopWords = [];
        /** @var StopWordInterface $stopWord */
        foreach ($stopWordCollection->getItems() as $stopWord) {
            $stopWords[] = $this->getById($stopWord->getId());
        }
        $searchResults->setItems($stopWords);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection  $stopWordCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $stopWordCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $stopWordCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection $stopWordCollection
     */
    private function addOrderToCollection($sortOrders, Collection $stopWordCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $stopWordCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? 'DESC' : 'ASC'
            );
        }
    }

    /**
     * @param int $storeId
     * @return array
     */
    public function getArrayListByStoreId($storeId)
    {
        $result = [];
        /** @var \Amasty\ElasticSearch\Model\ResourceModel\StopWord\Collection $stopWordCollection */
        $stopWordCollection = $this->stopWordCollectionFactory->create();
        $stopWordCollection->addFieldToFilter(StopWordInterface::STORE_ID, $storeId);

        /** @var StopWordInterface $item */
        foreach ($stopWordCollection as $item) {
            $result[] = $item->getTerm();
        }

        return $result;
    }

    /**
     * @param $file
     * @param $storeId
     * @return int
     * @throws \Exception
     */
    public function importStopWords($file, $storeId)
    {
        $count = 0;
        $csvData = $this->csv->getData($file);
        foreach ($csvData as $data) {
            if (isset($data[0])) {
                try {
                    $model = $this->stopWordFactory->create()->setStoreId($storeId)->setTerm($data[0]);
                    $this->save($model);
                } catch (AlreadyExistsException $e) {
                    continue;
                } catch (CouldNotSaveException $ex) {
                    continue;
                }
                $count++;
            }
        }

        return $count;
    }
}
