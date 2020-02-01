<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\ResourceModel;

use Aheadworks\Blog\Api\Data\AuthorInterface;
use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\Api\Data\ConditionInterface;
use Aheadworks\Blog\Api\Data\PostInterfaceFactory;
use Aheadworks\Blog\Api\Data\AuthorInterfaceFactory;
use Aheadworks\Blog\Api\Data\PostSearchResultsInterfaceFactory;
use Aheadworks\Blog\Model\PostFactory;
use Aheadworks\Blog\Model\PostRegistry;
use Aheadworks\Blog\Model\Source\Post\Status as PostStatus;
use Aheadworks\Blog\Model\Source\Post\CustomerGroups;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Aheadworks\Blog\Model\Converter\Condition as ConditionConverter;
use Aheadworks\Blog\Model\Indexer\ProductPost\Processor;
use Aheadworks\Blog\Model\Serialize\SerializeInterface;
use Aheadworks\Blog\Model\Serialize\Factory as SerializeFactory;
use Aheadworks\Blog\Model\ResourceModel\Post\RelatedPost\Finder as RelatedPostFinder;
use Aheadworks\Blog\Api\PostRepositoryInterface;

/**
 * Post repository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PostRepository implements PostRepositoryInterface
{
    /**
     * @var PostFactory
     */
    private $postFactory;

    /**
     * @var PostInterfaceFactory
     */
    private $postDataFactory;

    /**
     * @var AuthorInterfaceFactory
     */
    private $authorDataFactory;

    /**
     * @var PostRegistry
     */
    private $postRegistry;

    /**
     * @var PostSearchResultsInterfaceFactory
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
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ConditionConverter
     */
    private $conditionConverter;

    /**
     * @var Processor
     */
    private $indexProcessor;

    /**
     * @var SerializeInterface
     */
    private $serializer;

    /**
     * @var RelatedPostFinder
     */
    private $relatedPostFinder;

    /**
     * @param PostFactory $postFactory
     * @param PostInterfaceFactory $postDataFactory
     * @param AuthorInterfaceFactory $authorDataFactory
     * @param PostRegistry $postRegistry
     * @param PostSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param EntityManager $entityManager
     * @param ConditionConverter $conditionConverter
     * @param Processor $indexProcessor
     * @param SerializeFactory $serializeFactory
     * @param RelatedPostFinder $relatedPostFinder
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        PostFactory $postFactory,
        PostInterfaceFactory $postDataFactory,
        AuthorInterfaceFactory $authorDataFactory,
        PostRegistry $postRegistry,
        PostSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        EntityManager $entityManager,
        ConditionConverter $conditionConverter,
        Processor $indexProcessor,
        SerializeFactory $serializeFactory,
        RelatedPostFinder $relatedPostFinder
    ) {
        $this->postFactory = $postFactory;
        $this->postDataFactory = $postDataFactory;
        $this->authorDataFactory = $authorDataFactory;
        $this->postRegistry = $postRegistry;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->entityManager = $entityManager;
        $this->conditionConverter = $conditionConverter;
        $this->indexProcessor = $indexProcessor;
        $this->serializer = $serializeFactory->create();
        $this->relatedPostFinder = $relatedPostFinder;
    }

    /**
     * {@inheritdoc}
     */
    public function save(PostInterface $post)
    {
        $origPostData = null;
        /** @var \Aheadworks\Blog\Model\Post $postModel */
        $postModel = $this->postFactory->create();
        if ($postId = $post->getId()) {
            $this->entityManager->load($postModel, $postId);
            $origPostData = $postModel->getData();
        }
        $this->dataObjectHelper->populateWithArray(
            $postModel,
            $this->dataObjectProcessor->buildOutputDataArray($post, PostInterface::class),
            PostInterface::class
        );
        if ($postModel->getStatus() == PostStatus::DRAFT) {
            $postModel->setPublishDate(null);
        }
        $productCondition = $this->dataObjectProcessor->buildOutputDataArray(
            $postModel->getProductCondition(),
            ConditionInterface::class
        );
        if (is_array($productCondition)) {
            $postModel->setProductCondition($this->serializer->serialize($productCondition));
        }
        $this->checkAtCharacterForTwitterData($postModel);
        $this->checkCustomerGroupsData($postModel);
        $this->entityManager->save($postModel);
        $post = $this->convertPostConditionsToDataModel($postModel);
        $this->postRegistry->push($post);
        if ($this->isPostParamsChanged($post, $origPostData)) {
            if ($this->indexProcessor->isIndexerScheduled()) {
                $this->indexProcessor->markIndexerAsInvalid();
            } else {
                $this->indexProcessor->reindexRow($post->getId());
            }
        }

        return $post;
    }

    /**
     * {@inheritdoc}
     */
    public function get($postId)
    {
        if (null === $this->postRegistry->retrieve($postId)) {
            /** @var PostInterface $postModel */
            $postModel = $this->postDataFactory->create();
            $this->entityManager->load($postModel, $postId);
            if (!$postModel->getId()) {
                throw NoSuchEntityException::singleField('postId', $postId);
            } else {
                $postModel = $this->convertPostConditionsToDataModel($postModel);
                $this->postRegistry->push($postModel);
            }
        }
        return $this->postRegistry->retrieve($postId);
    }

    /**
     * {@inheritdoc}
     */
    public function getByUrlKey($postUrlKey)
    {
        $postModel = $this->postFactory->create();
        $postModel->loadByUrlKey($postUrlKey);
        if (!$postModel->getId()) {
            throw NoSuchEntityException::singleField('urlKey', $postUrlKey);
        } else {
            $postModel = $this->convertPostConditionsToDataModel($postModel);
        }

        return $postModel;
    }

    /**
     * {@inheritdoc}
     */
    public function getWithRelatedPosts($postId, $storeId, $customerGroupId)
    {
        $postModel = $this->get($postId);
        $relatedPostIds = $this->relatedPostFinder->find($postId, $storeId, $customerGroupId);
        $postModel->setRelatedPostIds($relatedPostIds);

        return $postModel;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Aheadworks\Blog\Api\Data\PostSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create()
            ->setSearchCriteria($searchCriteria);
        /** @var \Aheadworks\Blog\Model\ResourceModel\Post\Collection $collection */
        $collection = $this->postFactory->create()->getCollection();
        $this->extensionAttributesJoinProcessor->process($collection, PostInterface::class);
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() == PostInterface::STORE_IDS) {
                    $collection->addStoreFilter($filter->getValue());
                } elseif ($filter->getField() == 'tag_id') {
                    $collection->addTagFilter($filter->getValue());
                } elseif ($filter->getField() == 'product_id') {
                    $collection->addRelatedProductFilter($filter->getValue());
                } elseif ($filter->getField() == PostInterface::CUSTOMER_GROUPS) {
                    $collection->addCustomerGroupFilter($filter->getValue());
                } elseif ($filter->getField() == PostInterface::CATEGORY_IDS) {
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

        $posts = [];
        /** @var \Aheadworks\Blog\Model\Post $postModel */
        foreach ($collection as $postModel) {
            $postModel = $this->convertPostConditionsToDataModel($postModel);
            $posts[] = $this->prepareAuthor($postModel);
        }

        $searchResults->setItems($posts);
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(PostInterface $post)
    {
        return $this->deleteById($post->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($postId)
    {
        /** @var \Aheadworks\Blog\Model\Post $postModel */
        $postModel = $this->postFactory->create();
        $this->entityManager->load($postModel, $postId);
        if (!$postModel->getId()) {
            throw NoSuchEntityException::singleField('postId', $postId);
        }
        $this->entityManager->delete($postModel);
        $this->postRegistry->remove($postId);
        return true;
    }

    /**
     * Convert post conditions from array to data model
     *
     * @param PostInterface $post
     * @return PostInterface
     */
    private function convertPostConditionsToDataModel(PostInterface $post)
    {
        if ($post->getProductCondition()) {
            $conditionArray = $this->serializer->unserialize($post->getProductCondition());
            $conditionDataModel = $this->conditionConverter
                ->arrayToDataModel($conditionArray);
            $post->setProductCondition($conditionDataModel);
        } else {
            $post->setProductCondition('');
        }

        return $post;
    }

    /**
     * Prepare author
     *
     * @param PostInterface $post
     * @return PostInterface
     */
    private function prepareAuthor(PostInterface $post)
    {
        if (!empty($post->getAuthor())) {
            $author = $this->authorDataFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $author,
                $post->getAuthor(),
                AuthorInterface::class
            );
            $post->setAuthor($author);
        }

        return $post;
    }

    /**
     * If the necessary post parameters have been changed
     *
     * @param PostInterface $post
     * @param array $origPostData
     * @return bool
     */
    private function isPostParamsChanged($post, $origPostData)
    {
        if (!$origPostData) {
            return true;
        }
        $origPost = $this->postDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $origPost,
            $origPostData,
            PostInterface::class
        );
        $origPost = $this->convertPostConditionsToDataModel($origPost);
        if ($post->getProductCondition() != $origPost->getProductCondition()) {
            return true;
        }
        if ($post->getStoreIds() != $origPost->getStoreIds()) {
            return true;
        }
        return false;
    }

    /**
     * Check if twitter value contains @ symbol at the beginning
     *
     * @param PostInterface $post
     */
    private function checkAtCharacterForTwitterData(PostInterface $post)
    {
        if ($metaTwitterSite = $post->getMetaTwitterSite()) {
            $post->setMetaTwitterSite($this->insertAtCharacter($metaTwitterSite));
        }
        if ($metaTwitterCreator = $post->getMetaTwitterCreator()) {
            $post->setMetaTwitterCreator($this->insertAtCharacter($metaTwitterCreator));
        }
    }

    /**
     * Insert @ symbol to the string
     *
     * @param string $twitterValue
     * @return string
     */
    private function insertAtCharacter($twitterValue)
    {
        $at_character = '@';
        if ($twitterValue[0] != $at_character) {
            $twitterValue = $at_character . $twitterValue;
        }
        return $twitterValue;
    }

    /**
     * Check customer groups data and convert it from array to string.
     *
     * @param PostInterface $post
     */
    private function checkCustomerGroupsData(PostInterface $post)
    {
        $customerGroups = $post->getCustomerGroups();
        if (is_array($customerGroups) && in_array(CustomerGroups::ALL_GROUPS, $customerGroups)
            || (!is_array($customerGroups) || empty($customerGroups))
        ) {
            $customerGroups = [0 => CustomerGroups::ALL_GROUPS];
        }
        $post->setCustomerGroups(implode(',', $customerGroups));
    }
}
