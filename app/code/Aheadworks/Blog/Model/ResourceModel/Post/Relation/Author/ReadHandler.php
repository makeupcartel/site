<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\ResourceModel\Post\Relation\Author;

use Aheadworks\Blog\Api\Data\PostInterface;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Aheadworks\Blog\Api\AuthorRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class ReadHandler
 * @package Aheadworks\Blog\Model\ResourceModel\Post\Relation\Author
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var AuthorRepositoryInterface
     */
    private $authorRepository;

    /**
     * @param AuthorRepositoryInterface $authorRepository
     */
    public function __construct(AuthorRepositoryInterface $authorRepository)
    {
        $this->authorRepository = $authorRepository;
    }

    /**
     * {@inheritdoc}
     * @param PostInterface $entity
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        if ($entityId = (int)$entity->getId() && $entity->getAuthorId()) {
            try {
                $author = $this->authorRepository->get($entity->getAuthorId());
                $entity->setAuthor($author);
            } catch (LocalizedException $e) {
            }
        }
        return $entity;
    }
}
