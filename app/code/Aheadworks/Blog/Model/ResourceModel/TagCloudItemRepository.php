<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\ResourceModel;

use Aheadworks\Blog\Api\Data\TagInterface;
use Aheadworks\Blog\Api\Data\TagCloudItemInterface;
use Aheadworks\Blog\Api\Data\TagCloudItemSearchResultsInterfaceFactory;
use Aheadworks\Blog\Model\TagRegistry;
use Aheadworks\Blog\Model\ResourceModel\TagCloudItem as ResourceTagCloudItem;
use Aheadworks\Blog\Model\ResourceModel\TagCloudItem\CollectionFactory as TagCloudItemCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;

/**
 * Tag Cloud Item repository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TagCloudItemRepository implements \Aheadworks\Blog\Api\TagCloudItemRepositoryInterface
{
    /**
     * @var \Aheadworks\Blog\Model\Data\TagCloudItemFactory
     */
    private $tagCloudItemFactory;

    /**
     * @var \Aheadworks\Blog\Model\Data\TagFactory
     */
    private $tagDataFactory;

    /**
     * @var TagRegistry
     */
    private $tagRegistry;

    /**
     * @var ResourceTagCloudItem
     */
    private $tagCloudItemResourceModel;

    /**
     * @var TagCloudItemCollectionFactory
     */
    private $tagCloudItemCollectionFactory;

    /**
     * @var TagCloudItemSearchResultsInterface
     */
    private $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @param \Aheadworks\Blog\Model\Data\TagCloudItemFactory $tagCloudItemFactory
     * @param \Aheadworks\Blog\Model\Data\TagFactory $tagDataFactory
     * @param TagRegistry $tagRegistry
     * @param ResourceTagCloudItem $tagCloudItemResourceModel
     * @param TagCloudItemCollectionFactory $tagCloudItemCollectionFactory
     * @param TagCloudItemSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     */
    public function __construct(
        \Aheadworks\Blog\Model\Data\TagCloudItemFactory $tagCloudItemFactory,
        \Aheadworks\Blog\Model\Data\TagFactory $tagDataFactory,
        TagRegistry $tagRegistry,
        ResourceTagCloudItem $tagCloudItemResourceModel,
        TagCloudItemCollectionFactory $tagCloudItemCollectionFactory,
        TagCloudItemSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        JoinProcessorInterface $extensionAttributesJoinProcessor
    ) {
        $this->tagCloudItemFactory = $tagCloudItemFactory;
        $this->tagDataFactory = $tagDataFactory;
        $this->tagRegistry = $tagRegistry;
        $this->tagCloudItemResourceModel = $tagCloudItemResourceModel;
        $this->tagCloudItemCollectionFactory = $tagCloudItemCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function get($tagId, $storeId)
    {
        /** @var TagCloudItemInterface $tagCloudItem */
        $tagCloudItem = $this->tagCloudItemFactory->create();
        $tagCloudItem
            ->setTag($this->tagRegistry->retrieve($tagId))
            ->setPostCount(
                $this->tagCloudItemResourceModel->getPostCount($tagId, $storeId)
            );
        return $tagCloudItem;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria, $storeId)
    {
        /** @var \Aheadworks\Blog\Api\Data\TagCloudItemSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create()
            ->setSearchCriteria($searchCriteria);
        /** @var \Aheadworks\Blog\Model\ResourceModel\TagCloudItem\Collection $collection */
        $collection = $this->tagCloudItemCollectionFactory->create();
        $collection->joinPostCount($storeId);
        $this->extensionAttributesJoinProcessor->process($collection, TagCloudItemInterface::class);
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() == 'category_id') {
                    $collection->addCategoryFilter($filter->getValue());
                } else {
                    $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                    $fields[] = $filter->getField();
                    $conditions[] = [$condition => $filter->getValue()];
                }
            }
            if ($fields) {
                $collection->addFieldToFilter($fields, $conditions);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        if ($sortOrders = $searchCriteria->getSortOrders()) {
            /** @var \Magento\Framework\Api\SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder($sortOrder->getField(), $sortOrder->getDirection());
            }
        }
        $collection
            ->setCurPage($searchCriteria->getCurrentPage())
            ->setPageSize($searchCriteria->getPageSize());

        $tagCloudItems = [];
        /** @var \Aheadworks\Blog\Model\Tag $tagModel */
        foreach ($collection as $tagModel) {
            $tagCloudItems[] = $this->getTagCloudItem($tagModel);
        }
        $searchResults->setItems($tagCloudItems);
        $searchResults->setMaxPostCount($collection->getMaxPostCount());
        $searchResults->setMinPostCount($collection->getMinPostCount());
        return $searchResults;
    }

    /**
     * Retrieves tag cloud item instance using Tag Model
     *
     * @param \Aheadworks\Blog\Model\Tag $tagModel
     * @return TagCloudItemInterface
     */
    private function getTagCloudItem(\Aheadworks\Blog\Model\Tag $tagModel)
    {
        /** @var TagInterface $tag */
        $tag = $this->tagDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $tag,
            $tagModel->getData(),
            TagInterface::class
        );
        /** @var TagCloudItemInterface $tagCloudItem */
        $tagCloudItem = $this->tagCloudItemFactory->create();
        $tagCloudItem->setTag($tag);
        $this->dataObjectHelper->populateWithArray(
            $tagCloudItem,
            $tagModel->getData(),
            TagCloudItemInterface::class
        );
        return $tagCloudItem;
    }
}
