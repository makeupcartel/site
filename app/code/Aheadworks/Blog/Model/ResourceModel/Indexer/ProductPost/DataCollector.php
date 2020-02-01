<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\ResourceModel\Indexer\ProductPost;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\Api\PostRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;

/**
 * Class DataCollector
 *
 * @package Aheadworks\Blog\Model\ResourceModel\Indexer\ProductPost
 */
class DataCollector
{
    /**
     * @var PostRepositoryInterface
     */
    private $postRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param PostRepositoryInterface $postRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        PostRepositoryInterface $postRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreManagerInterface $storeManager
    ) {
        $this->postRepository = $postRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
    }

    /**
     * Prepare and return data for insert to index table
     *
     * @param array|null $entityIds
     * @return array
     * @throws LocalizedException
     */
    public function prepareProductPostData($entityIds = null)
    {
        if ($entityIds) {
            $this->searchCriteriaBuilder->addFilter(PostInterface::ID, $entityIds, 'in');
        }
        $postList = $this->postRepository
            ->getList($this->searchCriteriaBuilder->create())
            ->getItems();

        $data = [];
        foreach ($postList as $post) {
            if ($post->getProductRule()->getConditions()->getConditions()) {
                $websiteIds = $this->getWebsiteIdsByStoreIds($post->getStoreIds());
                $productIds = $post->getProductRule()->setWebsiteIds($websiteIds)->getProductIds();

                foreach ($productIds as $productId => $validationByWebsite) {
                    foreach ($websiteIds as $websiteId) {
                        if ($stores = $this->getWebsiteStores($websiteId)) {
                            if (empty($validationByWebsite[$websiteId])) {
                                continue;
                            }
                            /** @var Store $store */
                            foreach ($stores as $store) {
                                $data[] = [
                                    'product_id' => $productId,
                                    'post_id' => $post->getId(),
                                    'store_id' => $store->getId()
                                ];
                            }
                        }
                    }
                }
            }
        }
        return $data;
    }

    /**
     * Get stores associated with website
     *
     * @param int $websiteId
     * @return array|bool
     */
    private function getWebsiteStores($websiteId)
    {
        try {
            return $this->storeManager->getWebsite($websiteId)->getStores();
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Retrieve website ids by store ids
     *
     * @param array $storeIds
     * @return array
     */
    private function getWebsiteIdsByStoreIds($storeIds)
    {
        $websiteIds = [];
        foreach ($storeIds as $storeId) {
            if ($storeId == 0) {
                foreach ($this->storeManager->getWebsites() as $website) {
                    $websiteIds[] = $website->getId();
                }
            } else {
                try {
                    $websiteIds[] = $this->storeManager->getStore($storeId)->getWebsiteId();
                } catch (NoSuchEntityException $e) {
                }
            }
        }

        return array_unique($websiteIds);
    }
}
