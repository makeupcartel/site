<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Block\Widget;

use Aheadworks\Blog\Api\Data\TagCloudItemInterface;
use Aheadworks\Blog\Api\Data\TagCloudItemSearchResultsInterface;
use Aheadworks\Blog\Api\TagCloudItemRepositoryInterface;
use Aheadworks\Blog\Model\Config;
use Aheadworks\Blog\Model\Url;
use Magento\Widget\Block\BlockInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Api\SortOrder;

/**
 * Tag Cloud Widget
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TagCloud extends \Magento\Framework\View\Element\Template implements BlockInterface, IdentityInterface
{
    /**
     * Default value of lower tag weight offset
     */
    const DEFAULT_MIN_WEIGHT = 0.72;

    /**
     * Default value of upper tag weight offset
     */
    const DEFAULT_MAX_WEIGHT = 1.28;

    /**
     * Default value of slope tag weight curve
     */
    const DEFAULT_SLOPE = 0.1;

    /**
     * @var TagCloudItemRepositoryInterface
     */
    private $tagCloudItemRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var int|null
     */
    private $minPostCount = null;

    /**
     * @var int|null
     */
    private $maxPostCount = null;

    /**
     * Tag cloud items search results cache
     *
     * @var TagCloudItemSearchResultsInterface|null
     */
    private $searchResults = null;

    /**
     * @param Context $context
     * @param TagCloudItemRepositoryInterface $tagCloudItemRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Config $config
     * @param Url $url
     * @param SortOrderBuilder $sortOrderBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        TagCloudItemRepositoryInterface $tagCloudItemRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Config $config,
        Url $url,
        SortOrderBuilder $sortOrderBuilder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->tagCloudItemRepository = $tagCloudItemRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->config = $config;
        $this->url = $url;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     * Retrieves maximal weight
     *
     * @return float
     */
    private function getMaxWeight()
    {
        return $this->getData('max_weight') ?: self::DEFAULT_MAX_WEIGHT;
    }

    /**
     * Retrieves minimal weight
     *
     * @return float
     */
    private function getMinWeight()
    {
        return $this->getData('min_weight') ?: self::DEFAULT_MIN_WEIGHT;
    }

    /**
     * Retrieves slope
     *
     * @return float
     */
    private function getSlope()
    {
        return $this->getData('slope') ?: self::DEFAULT_SLOPE;
    }

    /**
     * Retrieves minimal number of posts
     *
     * @return int|null
     */
    private function getMinPostCount()
    {
        if (!$this->minPostCount) {
            $this->minPostCount = $this->getSearchResults()->getMinPostCount();
        }
        return $this->minPostCount;
    }

    /**
     * Retrieves maximal number of posts
     *
     * @return int|null
     */
    private function getMaxPostCount()
    {
        if (!$this->maxPostCount) {
            $this->maxPostCount = $this->getSearchResults()->getMaxPostCount();
        }
        return $this->maxPostCount;
    }

    /**
     * Get tag cloud items search results
     *
     * @return TagCloudItemSearchResultsInterface|null
     */
    private function getSearchResults()
    {
        if (!$this->searchResults) {
            $sortOrder = $this->sortOrderBuilder
                ->setField('post_count')
                ->setDirection(SortOrder::SORT_DESC)
                ->create();
            $this->searchCriteriaBuilder
                ->addSortOrder($sortOrder)
                ->setPageSize($this->config->getNumPopularTags());

            if ($categoryId = $this->getRequest()->getParam('blog_category_id')) {
                $this->searchCriteriaBuilder->addFilter('category_id', $categoryId);
            }
            $this->searchResults = $this->tagCloudItemRepository->getList(
                $this->searchCriteriaBuilder->create(),
                $this->_storeManager->getStore()->getId()
            );
        }
        return $this->searchResults;
    }

    /**
     * @return TagCloudItemInterface[]
     */
    public function getItems()
    {
        return $this->getSearchResults()->getItems();
    }

    /**
     * Checks whether Tag Cloud widget is enabled or not
     * @return bool
     */
    public function isEnabled()
    {
        return $this->config->isBlogEnabled();
    }

    /**
     * @return bool
     */
    public function isCloudMode()
    {
        return $this->config->isHighlightTags();
    }

    /**
     * Get tag weight
     *
     * @param TagCloudItemInterface $tagCloudItem
     * @return int
     */
    public function getWeight(TagCloudItemInterface $tagCloudItem)
    {
        $count = $tagCloudItem->getPostCount();
        $averageCount = (int)($this->getMaxPostCount() + $this->getMinPostCount()) / 2;

        $weightOffset = $count >= $averageCount ? $this->getMaxWeight() : $this->getMinWeight();
        $countOffset = $averageCount - $this->getSlope() / ($weightOffset - 1);
        $weight = $weightOffset - $this->getSlope() / ($count - $countOffset);

        return round($weight, 2) * 100;
    }

    /**
     * @param TagCloudItemInterface|string $tagCloudItem
     * @return string
     */
    public function getSearchByTagUrl($tagCloudItem)
    {
        return $this->url->getSearchByTagUrl($tagCloudItem->getTag());
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        $identities = [\Aheadworks\Blog\Model\Post::CACHE_TAG];
        foreach ($this->getItems() as $tagCloudItem) {
            $identities[] = \Aheadworks\Blog\Model\Tag::CACHE_TAG . '_' . $tagCloudItem->getTag()->getId();
        }
        return $identities;
    }
}
