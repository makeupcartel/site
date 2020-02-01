<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model;

use Aheadworks\Blog\Api\Data\AuthorInterface;
use Aheadworks\Blog\Api\Data\CategoryInterface;
use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\Api\Data\TagInterface;
use Aheadworks\Blog\App\Router\Matcher\Tag;
use Magento\Framework\UrlInterface;
use Aheadworks\Blog\Model\Url\Canonical\Category as CanonicalCategory;

/**
 * Class Url
 * @package Aheadworks\Blog\Model
 */
class Url
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var CanonicalCategory
     */
    private $canonicalCategory;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param Config $config
     * @param UrlInterface $urlBuilder
     * @param CanonicalCategory $canonicalCategory
     */
    public function __construct(
        Config $config,
        UrlInterface $urlBuilder,
        CanonicalCategory $canonicalCategory
    ) {
        $this->config = $config;
        $this->canonicalCategory = $canonicalCategory;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Retrieves url
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    private function getUrl($route, $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }

    /**
     * Retrieves blog home url
     *
     * @return string
     */
    public function getBlogHomeUrl()
    {
        return $this->getUrl(null, ['_direct' => $this->config->getRouteToBlog() . '/']);
    }

    /**
     * Retrieves authors url
     *
     * @return string
     */
    public function getAuthorsPageUrl()
    {
        return $this->getUrl(null, [
            '_direct' => $this->config->getRouteToBlog() . '/' . $this->config->getRouteToAuthors() . '/'
        ]);
    }

    /**
     * Retrieves post url
     *
     * @param PostInterface $post
     * @param CategoryInterface|null $category
     * @return string
     */
    public function getPostUrl(PostInterface $post, CategoryInterface $category = null)
    {
        $parts = [$this->config->getRouteToBlog()];
        if ($category) {
            $parts[] = $category->getUrlKey();
        }
        $parts[] = $post->getUrlKey();
        return $this->getUrl(null, ['_direct' => implode('/', $parts) . '/']);
    }

    /**
     * @param PostInterface $post
     * @param int|null $storeId
     * @return string
     */
    public function getPostRoute(PostInterface $post, $storeId = null)
    {
        return $this->config->getRouteToBlog($storeId) . '/' . $post->getUrlKey() . '/';
    }

    /**
     * Retrieves category url
     *
     * @param CategoryInterface $category
     * @return string
     */
    public function getCategoryUrl(CategoryInterface $category)
    {
        return $this->getUrl(null, ['_direct' => $this->getCategoryRoute($category)]);
    }

    /**
     * @param CategoryInterface $category
     * @param int|null $storeId
     * @return string
     */
    public function getCategoryRoute(CategoryInterface $category, $storeId = null)
    {
        return $this->config->getRouteToBlog($storeId) . '/' . $category->getUrlKey() . '/';
    }

    /**
     * Retrieve author url
     *
     * @param AuthorInterface $author
     * @return string
     */
    public function getAuthorUrl(AuthorInterface $author)
    {
        return $this->getUrl(null, ['_direct' => $this->getAuthorRoute($author)]);
    }

    /**
     * Retrieve author route
     *
     * @param AuthorInterface $author
     * @param int|null $storeId
     * @return string
     */
    public function getAuthorRoute(AuthorInterface $author, $storeId = null)
    {
        return $this->config->getRouteToBlog($storeId) . '/'
            . $this->config->getRouteToAuthors($storeId) . '/'
            . $author->getUrlKey() . '/';
    }

    /**
     * Retrieves search by tag url
     *
     * @param TagInterface|string $tag
     * @return string
     */
    public function getSearchByTagUrl($tag)
    {
        $tagName = $tag instanceof TagInterface ? $tag->getName() : $tag;
        return $this->getUrl(
            null,
            ['_direct' => $this->config->getRouteToBlog() . '/' . Tag::TAG_KEY . '/' . urlencode($tagName) . '/']
        );
    }

    /**
     * Get canonical URL of post
     *
     * @param PostInterface $post
     * @return string
     */
    public function getCanonicalUrl(PostInterface $post)
    {
        if ($category = $this->canonicalCategory->getCanonicalCategory($post)) {
             return $this->getPostUrl($post, $category);
        }
        return $this->getPostUrl($post);
    }
}
