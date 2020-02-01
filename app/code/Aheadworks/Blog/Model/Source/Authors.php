<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Source;

use Aheadworks\Blog\Api\Data\AuthorInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Aheadworks\Blog\Model\ResourceModel\Author\CollectionFactory;
use Aheadworks\Blog\Model\ResourceModel\Author\Collection;

/**
 * Class Authors
 * @package Aheadworks\Blog\Model\Source
 */
class Authors implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var array
     */
    private $options;

    /**
     * @param CollectionFactory $collectionFactoryFactory
     */
    public function __construct(CollectionFactory $collectionFactoryFactory)
    {
        $this->collectionFactory = $collectionFactoryFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $options = [];
            /** @var Collection $collection */
            $collection = $this->collectionFactory->create();
            $collection->addOrder(AuthorInterface::FIRSTNAME, 'ASC');
            /** @var AuthorInterface $author */
            foreach ($collection as $author) {
                $options[] = [
                    'label' => $author->getFirstname() . ' ' . $author->getLastname(),
                    'value' => $author->getId()
                ];
            }
            $this->options = $options;
        }

        return $this->options;
    }

    /**
     * @return array
     */
    public function getAvailableOptions()
    {
        $options = [];
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addOrder(AuthorInterface::FIRSTNAME, 'ASC');
        /** @var AuthorInterface $author */
        foreach ($collection as $author) {
            $options[$author->getId()] = $author->getFirstname() . ' ' . $author->getLastname();
        }

        return $options;
    }
}
