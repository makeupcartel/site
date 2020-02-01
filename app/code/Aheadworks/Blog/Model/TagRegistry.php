<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model;

use Aheadworks\Blog\Api\Data\TagInterface;
use Aheadworks\Blog\Api\Data\TagInterfaceFactory;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Registry for \Aheadworks\Blog\Api\Data\TagInterface
 */
class TagRegistry
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var TagInterfaceFactory
     */
    private $tagDataFactory;

    /**
     * @var array
     */
    private $tagRegistry = [];

    /**
     * @param EntityManager $entityManager
     * @param TagInterfaceFactory $tagDataFactory
     */
    public function __construct(
        EntityManager $entityManager,
        TagInterfaceFactory $tagDataFactory
    ) {
        $this->entityManager = $entityManager;
        $this->tagDataFactory = $tagDataFactory;
    }

    /**
     * Retrieve Tag from registry
     *
     * @param int $tagId
     * @return TagInterface
     * @throws NoSuchEntityException
     */
    public function retrieve($tagId)
    {
        if (!isset($this->tagRegistry[$tagId])) {
            /** @var TagInterface $tag */
            $tag = $this->tagDataFactory->create();
            $this->entityManager->load($tag, $tagId);
            if (!$tag->getId()) {
                throw NoSuchEntityException::singleField('tagId', $tagId);
            } else {
                $this->tagRegistry[$tagId] = $tag;
            }
        }
        return $this->tagRegistry[$tagId];
    }

    /**
     * Remove instance of the Tag from registry
     *
     * @param int $tagId
     * @return void
     */
    public function remove($tagId)
    {
        if (isset($this->tagRegistry[$tagId])) {
            unset($this->tagRegistry[$tagId]);
        }
    }

    /**
     * Replace existing Tag with a new one
     *
     * @param TagInterface $tag
     * @return $this
     */
    public function push(TagInterface $tag)
    {
        if ($tagId = $tag->getId()) {
            $this->tagRegistry[$tagId] = $tag;
        }
        return $this;
    }
}
