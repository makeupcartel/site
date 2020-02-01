<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model;

use Aheadworks\Blog\Api\Data\AuthorInterface;
use Aheadworks\Blog\Api\Data\AuthorInterfaceFactory;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class AuthorRegistry
 * @package Aheadworks\Blog\Model
 */
class AuthorRegistry
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var AuthorInterfaceFactory
     */
    private $authorDataFactory;

    /**
     * @var array
     */
    private $authorRegistry = [];

    /**
     * @param EntityManager $entityManager
     * @param AuthorInterfaceFactory $authorDataFactory
     */
    public function __construct(
        EntityManager $entityManager,
        AuthorInterfaceFactory $authorDataFactory
    ) {
        $this->entityManager = $entityManager;
        $this->authorDataFactory = $authorDataFactory;
    }

    /**
     * Retrieve Author from registry
     *
     * @param int $authorId
     * @return AuthorInterface
     * @throws NoSuchEntityException
     */
    public function retrieve($authorId)
    {
        if (!isset($this->authorRegistry[$authorId])) {
            /** @var AuthorInterface $author */
            $author = $this->authorDataFactory->create();
            $this->entityManager->load($author, $authorId);
            if (!$author->getId()) {
                throw NoSuchEntityException::singleField(AuthorInterface::ID, $authorId);
            } else {
                $this->authorRegistry[$authorId] = $author;
            }
        }
        return $this->authorRegistry[$authorId];
    }

    /**
     * Remove instance of the Author from registry
     *
     * @param int $authorId
     * @return void
     */
    public function remove($authorId)
    {
        if (isset($this->authorRegistry[$authorId])) {
            unset($this->authorRegistry[$authorId]);
        }
    }

    /**
     * Replace existing Author with a new one
     *
     * @param AuthorInterface $author
     * @return $this
     */
    public function push(AuthorInterface $author)
    {
        if ($authorId = $author->getId()) {
            $this->authorRegistry[$authorId] = $author;
        }
        return $this;
    }
}
