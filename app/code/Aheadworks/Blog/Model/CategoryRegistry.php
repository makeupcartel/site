<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model;

use Aheadworks\Blog\Api\Data\CategoryInterface;
use Aheadworks\Blog\Api\Data\CategoryInterfaceFactory;
use Aheadworks\Blog\Model\CategoryFactory;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Registry for \Aheadworks\Blog\Api\Data\CategoryInterface
 */
class CategoryRegistry
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CategoryInterfaceFactory
     */
    private $categoryDataFactory;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var array
     */
    private $categoryRegistry = [];

    /**
     * @var array
     */
    private $categoryRegistryByUrlKey = [];

    /**
     * @param EntityManager $entityManager
     * @param CategoryInterfaceFactory $categoryDataFactory
     * @param CategoryFactory $categoryFactory
     */
    public function __construct(
        EntityManager $entityManager,
        CategoryInterfaceFactory $categoryDataFactory,
        CategoryFactory $categoryFactory
    ) {
        $this->entityManager = $entityManager;
        $this->categoryDataFactory = $categoryDataFactory;
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * Retrieve Category from registry
     *
     * @param int $categoryId
     * @return CategoryInterface
     * @throws NoSuchEntityException
     */
    public function retrieve($categoryId)
    {
        if (!isset($this->categoryRegistry[$categoryId])) {
            /** @var Category $category */
            $category = $this->categoryDataFactory->create();
            $this->entityManager->load($category, $categoryId);
            if (!$category->getId()) {
                throw NoSuchEntityException::singleField('categoryId', $categoryId);
            } else {
                $this->categoryRegistry[$categoryId] = $category;
            }
        }
        return $this->categoryRegistry[$categoryId];
    }

    /**
     * Retrieve Category from registry by url key
     *
     * @param string $categoryUrlKey
     * @return CategoryInterface
     * @throws NoSuchEntityException
     */
    public function retrieveByUrl($categoryUrlKey)
    {
        if (!isset($this->categoryRegistryByUrlKey[$categoryUrlKey])) {
            /** @var Category $category */
            $category = $this->categoryFactory->create();
            $category->loadByUrlKey($categoryUrlKey);
            if (!$category->getId()) {
                throw NoSuchEntityException::singleField('categoryUrl', $categoryUrlKey);
            } else {
                $this->categoryRegistryByUrlKey[$categoryUrlKey] = $category;
            }
        }
        return $this->categoryRegistryByUrlKey[$categoryUrlKey];
    }

    /**
     * Remove instance of the Category from registry
     *
     * @param int $categoryId
     * @return void
     */
    public function remove($categoryId)
    {
        if (isset($this->categoryRegistry[$categoryId])) {
            unset($this->categoryRegistry[$categoryId]);
        }
    }

    /**
     * Replace existing Category with a new one
     *
     * @param CategoryInterface $category
     * @return $this
     */
    public function push(CategoryInterface $category)
    {
        if ($categoryId = $category->getId()) {
            $this->categoryRegistry[$categoryId] = $category;
        }
        return $this;
    }
}
