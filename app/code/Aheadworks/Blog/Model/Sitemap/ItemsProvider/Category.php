<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Sitemap\ItemsProvider;

use Aheadworks\Blog\Api\Data\CategoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Blog\Model\Source\Category\Status;

/**
 * Class Category
 * @package Aheadworks\Blog\Model\Sitemap\ItemsProvider
 */
class Category extends AbstractProvider implements ProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getItems($storeId)
    {
        $categoryItems = [];
        foreach ($this->getCategories($storeId) as $category) {
            $categoryItems[$category->getId()] = new DataObject(
                [
                    'id' => $category->getId(),
                    'url' => $this->url->getCategoryRoute($category, $storeId),
                    'updated_at' => $this->getCurrentDateTime()
                ]
            );
        }

        return [new DataObject(
            [
                'changefreq' => $this->getChangeFreq($storeId),
                'priority' => $this->getPriority($storeId),
                'collection' => $categoryItems
            ]
        )];
    }

    /**
     * {@inheritdoc}
     */
    public function getItems23x($storeId)
    {
        $categoryItems = [];
        foreach ($this->getCategories($storeId) as $category) {
            $categoryItems[] = $this->getSitemapItem(
                [
                    'url' => $this->url->getCategoryRoute($category, $storeId),
                    'priority' => $this->getPriority($storeId),
                    'changeFrequency' => $this->getChangeFreq($storeId),
                    'updatedAt' => $this->getCurrentDateTime()
                ]
            );
        }

        return $categoryItems;
    }

    /**
     * Retrieves list of categories
     *
     * @param int $storeId
     * @return CategoryInterface[]
     * @throws LocalizedException
     */
    private function getCategories($storeId)
    {
        $this->searchCriteriaBuilder
            ->addFilter('status', Status::ENABLED)
            ->addFilter(CategoryInterface::STORE_IDS, $storeId);

        return $this->categoryRepository->getList($this->searchCriteriaBuilder->create())->getItems();
    }
}
