<?php
/**
 * Copyright 2018 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Controller;

use Aheadworks\Blog\Model\Config;
use Magento\Framework\App\Action\Forward;

/**
 * Blog Router
 * @package Aheadworks\Blog\Controller
 */
class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var string
     */
    const TAG_KEY = 'tag';

    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    private $actionFactory;

    /**
     * @var \Aheadworks\Blog\Model\CategoryFactory
     */
    private $categoryModelFactory;

    /**
     * @var \Aheadworks\Blog\Model\PostFactory
     */
    private $postModelFactory;

    /**
     * @var \Aheadworks\Blog\Model\TagFactory
     */
    private $tagModelFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Aheadworks\Blog\Model\CategoryFactory $categoryModelFactory
     * @param \Aheadworks\Blog\Model\PostFactory $postModelFactory
     * @param \Aheadworks\Blog\Model\TagFactory $tagModelFactory
     * @param Config $config
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Aheadworks\Blog\Model\CategoryFactory $categoryModelFactory,
        \Aheadworks\Blog\Model\PostFactory $postModelFactory,
        \Aheadworks\Blog\Model\TagFactory $tagModelFactory,
        Config $config
    ) {
        $this->actionFactory = $actionFactory;
        $this->categoryModelFactory = $categoryModelFactory;
        $this->postModelFactory = $postModelFactory;
        $this->tagModelFactory = $tagModelFactory;
        $this->config = $config;
    }

    /**
     * Match blog pages
     *
     * @param \Magento\Framework\App\RequestInterface|\Magento\Framework\App\Request\Http $request
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->config->isBlogEnabled()) {
            return false;
        }
        $parts = explode('/', trim($request->getPathInfo(), '/'));
        if (array_shift($parts) != $this->config->getRouteToBlog()) {
            return false;
        }
        list($moduleName, $controllerName, $actionName, $params) = ['aw_blog', 'index', 'index', null];
        if (count($parts)) {
            $urlKey = array_shift($parts);
            if ($urlKey == self::TAG_KEY) {
                $tagName = array_shift($parts);
                if ($tagName) {
                    $params['tag_id'] = $this->getTagIdByName(urldecode($tagName));
                }
            } else {
                if ($postId = $this->getPostIdByUrlKey($urlKey)) {
                    list($controllerName, $actionName, $params) = ['post', 'view', ['post_id' => $postId]];
                } else {
                    if ($categoryId = $this->getCategoryIdByUrlKey($urlKey)) {
                        list($controllerName, $actionName) = ['category', 'view'];
                        $params['blog_category_id'] = $categoryId;

                        $postUrlKey = array_shift($parts);
                        if ($postUrlKey) {
                            if ($postId = $this->getPostIdByUrlKey($postUrlKey)) {
                                list($controllerName, $actionName) = ['post', 'view'];
                                $params['post_id'] = $postId;
                            }
                        }
                    } else {
                        list($moduleName, $controllerName, $actionName) = ['cms', 'noroute', 'index'];
                    }
                }
            }
        }

        $request
            ->setModuleName($moduleName)
            ->setControllerName($controllerName)
            ->setActionName($actionName);
        if ($params) {
            $request->setParams($params);
        }
        return $this->actionFactory->create(Forward::class);
    }

    /**
     * Retrieves post ID by URL-Key
     *
     * @param string $urlKey
     * @return int|null
     */
    private function getPostIdByUrlKey($urlKey)
    {
        // Intermediate solution
        /** @var \Aheadworks\Blog\Model\Post $postModel */
        $postModel = $this->postModelFactory->create();
        $postModel->load($urlKey, 'url_key');
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
        // Intermediate solution
        /** @var \Aheadworks\Blog\Model\Category $categoryModel */
        $categoryModel = $this->categoryModelFactory->create();
        $categoryModel->load($urlKey, 'url_key');
        return $categoryModel->getId();
    }

    /**
     * Retrieves tag ID by name
     *
     * @param string $tagName
     * @return int|null
     */
    private function getTagIdByName($tagName)
    {
        // Intermediate solution
        /** @var \Aheadworks\Blog\Model\Tag $tagModel */
        $tagModel = $this->tagModelFactory->create();
        $tagModel->load($tagName, 'name');
        return $tagModel->getId();
    }
}
