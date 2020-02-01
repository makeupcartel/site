<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\App\Router\Matcher;

use Aheadworks\Blog\Api\Data\CategoryInterface;
use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\App\Router\MatcherInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Aheadworks\Blog\Model\PostFactory;
use Aheadworks\Blog\Model\CategoryFactory;

/**
 * Class Category
 * @package Aheadworks\Blog\App\Router\Matcher
 */
class Category implements MatcherInterface
{
    /**
     * @var PostFactory
     */
    private $postFactory;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @param PostFactory $postFactory
     * @param CategoryFactory $categoryFactory
     */
    public function __construct(PostFactory $postFactory, CategoryFactory $categoryFactory)
    {
        $this->postFactory = $postFactory;
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * {@inheritdoc}
     * @param RequestInterface|Http $request
     */
    public function match(RequestInterface $request)
    {
        $parts = explode('/', trim($request->getPathInfo(), '/'));
        list(, $urlKey, $postUrlKey) = array_merge($parts, array_fill(0, 3, null));

        if ($categoryId = $this->getCategoryIdByUrlKey($urlKey)) {
            $controllerName = 'category';
            $params = ['blog_category_id' => $categoryId];

            if ($postUrlKey && $postId = $this->getPostIdByUrlKey($postUrlKey)) {
                $controllerName = 'post';
                $params['post_id'] = $postId;
            }

            $request
                ->setControllerName($controllerName)
                ->setActionName('view')
                ->setParams($params);

            return true;
        }

        return false;
    }

    /**
     * Retrieves post ID by URL-Key
     *
     * @param string $urlKey
     * @return int|null
     */
    private function getPostIdByUrlKey($urlKey)
    {
        /** @var \Aheadworks\Blog\Model\Post $postModel */
        $postModel = $this->postFactory->create();
        $postModel->load($urlKey, PostInterface::URL_KEY);
        return $postModel->getId();
    }

    /**
     * Retrieves category ID by URL-Key
     *
     * @param string $urlKey
     * @return int|null
     */
    private function getCategoryIdByUrlKey($urlKey)
    {
        /** @var \Aheadworks\Blog\Model\Category $categoryModel */
        $categoryModel = $this->categoryFactory->create();
        $categoryModel->load($urlKey, CategoryInterface::URL_KEY);
        return $categoryModel->getId();
    }
}
