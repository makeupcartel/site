<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Sitemap\ItemsProvider;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Aheadworks\Blog\Api\AuthorRepositoryInterface;
use Aheadworks\Blog\Api\CategoryRepositoryInterface;
use Aheadworks\Blog\Api\PostRepositoryInterface;
use Aheadworks\Blog\Model\Config;
use Aheadworks\Blog\Model\Url;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class AbstractProvider
 * @package Aheadworks\Blog\Model\Sitemap\ItemsProvider
 */
class AbstractProvider
{
    /**
     * Sitemap item factory class
     */
    const SITEMAP_ITEM_FACTORY_CLASS = 'Magento\Sitemap\Model\SitemapItemInterfaceFactory';

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var PostRepositoryInterface
     */
    protected $postRepository;

    /**
     * @var AuthorRepositoryInterface
     */
    protected $authorRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var Url
     */
    protected $url;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Magento\Sitemap\Model\SitemapItemInterfaceFactory
     */
    private $itemFactory;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @param PostRepositoryInterface $postRepository
     * @param AuthorRepositoryInterface $authorRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ObjectManagerInterface $objectManager
     * @param Url $url
     * @param Config $config
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        PostRepositoryInterface $postRepository,
        AuthorRepositoryInterface $authorRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ObjectManagerInterface $objectManager,
        Url $url,
        Config $config
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->postRepository = $postRepository;
        $this->authorRepository = $authorRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->objectManager = $objectManager;
        $this->url = $url;
        $this->config = $config;
    }

    /**
     * Get change frequency
     *
     * @param int $storeId
     * @return float
     */
    protected function getChangeFreq($storeId)
    {
        return $this->config->getSitemapChangeFrequency($storeId);
    }

    /**
     * Get priority
     *
     * @param int $storeId
     * @return string
     */
    protected function getPriority($storeId)
    {
        return $this->config->getSitemapPriority($storeId);
    }

    /**
     * Current date/time
     *
     * @return string
     */
    protected function getCurrentDateTime()
    {
        return (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT);
    }

    /**
     * Retrieve sitemap item 2.3.x compatibility
     *
     * @param array $itemData
     * @return \Magento\Sitemap\Model\SitemapItemInterface
     */
    protected function getSitemapItem($itemData)
    {
        if (!$this->itemFactory) {
            $this->itemFactory = $this->objectManager->create(self::SITEMAP_ITEM_FACTORY_CLASS);
        }

        return $this->itemFactory->create($itemData);
    }
}
