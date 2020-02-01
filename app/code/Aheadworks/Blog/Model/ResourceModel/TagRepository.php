<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\ResourceModel;

use Aheadworks\Blog\Api\Data\TagInterface;
use Aheadworks\Blog\Api\Data\TagInterfaceFactory;
use Aheadworks\Blog\Api\Data\TagSearchResultsInterfaceFactory;
use Aheadworks\Blog\Model\TagFactory;
use Aheadworks\Blog\Model\TagRegistry;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\EntityManager\EntityManager;

/**
 * Tag repository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TagRepository implements \Aheadworks\Blog\Api\TagRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var TagFactory
     */
    private $tagFactory;

    /**
     * @var TagInterfaceFactory
     */
    private $tagDataFactory;

    /**
     * @var TagRegistry
     */
    private $tagRegistry;

    /**
     * @var TagSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @param EntityManager $entityManager
     * @param TagFactory $tagFactory
     * @param TagInterfaceFactory $tagDataFactory
     * @param TagRegistry $tagRegistry
     * @param TagSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     */
    public function __construct(
        EntityManager $entityManager,
        TagFactory $tagFactory,
        TagInterfaceFactory $tagDataFactory,
        TagRegistry $tagRegistry,
        TagSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor
    ) {
        $this->entityManager = $entityManager;
        $this->tagFactory = $tagFactory;
        $this->tagDataFactory = $tagDataFactory;
        $this->tagRegistry = $tagRegistry;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function save(TagInterface $tag)
    {
        /** @var \Aheadworks\Blog\Model\Tag $tagModel */
        $tagModel = $this->tagFactory->create();
        if ($tagId = $tag->getId()) {
            $this->entityManager->load($tagModel, $tagId);
        }
        $this->dataObjectHelper->populateWithArray(
            $tagModel,
            $this->dataObjectProcessor->buildOutputDataArray($tag, TagInterface::class),
            TagInterface::class
        );
        $this->entityManager->save($tagModel);
        $tag = $this->getTagDataObject($tagModel);
        $this->tagRegistry->push($tag);
        return $tag;
    }

    /**
     * {@inheritdoc}
     */
    public function get($tagId)
    {
        return $this->tagRegistry->retrieve($tagId);
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Aheadworks\Blog\Api\Data\TagSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create()
            ->setSearchCriteria($searchCriteria);
        /** @var \Aheadworks\Blog\Model\ResourceModel\Tag\Collection $collection */
        $collection = $this->tagFactory->create()->getCollection();
        $this->extensionAttributesJoinProcessor->process($collection, TagInterface::class);
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $fields[] = $filter->getField();
                $conditions[] = [$condition => $filter->getValue()];
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

        $tags = [];
        /** @var \Aheadworks\Blog\Model\Tag $tagModel */
        foreach ($collection as $tagModel) {
            $tags[] = $this->getTagDataObject($tagModel);
        }
        $searchResults->setItems($tags);
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Aheadworks\Blog\Api\Data\TagInterface $tag)
    {
        return $this->deleteById($tag->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($tagId)
    {
        $tag = $this->tagRegistry->retrieve($tagId);
        $this->entityManager->delete($tag);
        $this->tagRegistry->remove($tagId);
        return true;
    }

    /**
     * Retrieves tag data object using Tag Model
     *
     * @param \Aheadworks\Blog\Model\Tag $tag
     * @return TagInterface
     */
    private function getTagDataObject(\Aheadworks\Blog\Model\Tag $tag)
    {
        /** @var TagInterface $tagDataObject */
        $tagDataObject = $this->tagDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $tagDataObject,
            $tag->getData(),
            TagInterface::class
        );
        return $tagDataObject;
    }
}
