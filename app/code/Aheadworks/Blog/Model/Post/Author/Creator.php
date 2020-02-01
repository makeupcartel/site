<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Post\Author;

use Aheadworks\Blog\Api\Data\AuthorInterface;
use Aheadworks\Blog\Api\Data\AuthorInterfaceFactory;
use Aheadworks\Blog\Api\AuthorRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Blog\Model\ResourceModel\Validator\UrlKeyIsUnique as UrlKeyValidator;

/**
 * Class Creator
 * @package Aheadworks\Blog\Model\Post\Author
 */
class Creator
{
    /**
     * Max tries
     */
    const MAX_TRIES = 10;

    /**
     * @var AuthorRepositoryInterface
     */
    private $authorRepository;

    /**
     * @var AuthorInterfaceFactory
     */
    private $authorDataFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var UrlKeyValidator
     */
    private $urlKeyValidator;

    /**
     * @param AuthorRepositoryInterface $authorRepository
     * @param AuthorInterfaceFactory $authorDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param UrlKeyValidator $urlKeyValidator
     */
    public function __construct(
        AuthorRepositoryInterface $authorRepository,
        AuthorInterfaceFactory $authorDataFactory,
        DataObjectHelper $dataObjectHelper,
        UrlKeyValidator $urlKeyValidator
    ) {
        $this->authorRepository = $authorRepository;
        $this->authorDataFactory = $authorDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->urlKeyValidator = $urlKeyValidator;
    }

    /**
     * Create author by full name
     *
     * @param string $fullName
     * @return AuthorInterface
     * @throws \Exception
     */
    public function createByName($fullName)
    {
        $nameParts = explode(' ', $fullName);
        if (count($nameParts) >= 2) {
            list ($firstName, $lastName) = $nameParts;
        } else {
            throw new LocalizedException(__('Author name is invalid.'));
        }

        $author = $this->prepareAuthor($firstName, $lastName);

        return $this->authorRepository->save($author);
    }

    /**
     * Prepare author
     *
     * @param string $firstName
     * @param string $lastName
     * @return AuthorInterface
     * @throws \Exception
     */
    private function prepareAuthor($firstName, $lastName)
    {
        /** @var AuthorInterface $author */
        $author = $this->authorDataFactory->create();
        $author
            ->setFirstname($firstName)
            ->setLastname($lastName);
        $newUrlKey = $urlKey = strtolower($firstName) . '-' . strtolower($lastName);
        $prepared = false;
        $counter = 1;
        do {
            $author->setUrlKey($newUrlKey);
            if ($this->urlKeyValidator->validate($author)) {
                $prepared = true;
            } else {
                $newUrlKey = $urlKey . '-' . $counter;
                $counter++;
            }
        } while (!$prepared && $counter <= self::MAX_TRIES);

        return $author;
    }
}
