<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Rss\Post;

use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Blog\Model\Rss\Post\RssItemFactory;
use Aheadworks\Blog\Model\Rss\Post\Processor\ProcessorInterface;
use Magento\Framework\EntityManager\Hydrator;
use Magento\Framework\Api\SimpleDataObjectConverter;

/**
 * Class RssItemProvider
 *
 * @package Aheadworks\Blog\Model\Rss\Post
 */
class RssItemProvider
{
    /**
     * @var RssItemFactory
     */
    private $rssItemFactory;

    /**
     * @var PostListing
     */
    private $postListing;

    /**
     * @var Hydrator
     */
    private $hydrator;

    /**
     * @var SimpleDataObjectConverter
     */
    private $dataObjectConverter;

    /**
     * @var ProcessorInterface[]
     */
    private $processors;

    /**
     * @param RssItemFactory $rssItemFactory
     * @param PostListing $postListing
     * @param Hydrator $hydrator
     * @param SimpleDataObjectConverter $dataObjectConverter
     * @param array $processors
     */
    public function __construct(
        RssItemFactory $rssItemFactory,
        PostListing $postListing,
        Hydrator $hydrator,
        SimpleDataObjectConverter $dataObjectConverter,
        array $processors = []
    ) {
        $this->rssItemFactory = $rssItemFactory;
        $this->postListing = $postListing;
        $this->hydrator = $hydrator;
        $this->dataObjectConverter = $dataObjectConverter;
        $this->processors = $processors;
    }

    /**
     * Get RSS items with post data
     *
     * @param int $storeId
     * @param int $customerGroupId
     * @return array
     * @throws LocalizedException
     */
    public function retrieveDataItems($storeId, $customerGroupId)
    {
        $data = [];

        $posts = $this->postListing->retrieveListOfPosts($storeId, $customerGroupId);
        foreach ($posts as $post) {
            $rssItem = $this->rssItemFactory->create();

            /** @var ProcessorInterface $processor */
            foreach ($this->processors as $processor) {
                $processor->process($rssItem, $post);
            }

            $rssItemAsArray = $this->hydrator->extract($rssItem);
            $data[] = $this->dataObjectConverter->convertKeysToCamelCase($rssItemAsArray);
        }

        return $data;
    }
}
