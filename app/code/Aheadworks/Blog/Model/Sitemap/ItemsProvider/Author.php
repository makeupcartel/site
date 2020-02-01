<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Sitemap\ItemsProvider;

use Aheadworks\Blog\Api\Data\AuthorInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Author
 * @package Aheadworks\Blog\Model\Sitemap\ItemsProvider
 */
class Author extends AbstractProvider implements ProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getItems($storeId)
    {
        $authorItems = [];
        foreach ($this->getAuthors() as $author) {
            $authorItems[$author->getId()] = new DataObject(
                [
                    'id' => $author->getId(),
                    'url' => $this->url->getAuthorRoute($author, $storeId),
                    'updated_at' => $this->getCurrentDateTime()
                ]
            );
        }

        return [new DataObject(
            [
                'changefreq' => $this->getChangeFreq($storeId),
                'priority' => $this->getPriority($storeId),
                'collection' => $authorItems
            ]
        )];
    }

    /**
     * {@inheritdoc}
     */
    public function getItems23x($storeId)
    {
        $authorItems = [];
        foreach ($this->getAuthors() as $author) {
            $authorItems[] = $this->getSitemapItem(
                [
                    'url' => $this->url->getAuthorRoute($author, $storeId),
                    'priority' => $this->getPriority($storeId),
                    'changeFrequency' => $this->getChangeFreq($storeId),
                    'updatedAt' => $this->getCurrentDateTime()
                ]
            );
        }

        return $authorItems;
    }

    /**
     * Retrieves list of authors
     *
     * @return AuthorInterface[]
     */
    private function getAuthors()
    {
        return $this->authorRepository->getList($this->searchCriteriaBuilder->create())->getItems();
    }
}
