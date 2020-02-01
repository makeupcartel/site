<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\App\Router\Matcher;

use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\App\Router\MatcherInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Aheadworks\Blog\Model\PostFactory;

/**
 * Class Post
 * @package Aheadworks\Blog\App\Router\Matcher
 */
class Post implements MatcherInterface
{
    /**
     * @var PostFactory
     */
    private $postFactory;

    /**
     * @param PostFactory $postFactory
     */
    public function __construct(PostFactory $postFactory)
    {
        $this->postFactory = $postFactory;
    }

    /**
     * {@inheritdoc}
     * @param RequestInterface|Http $request
     */
    public function match(RequestInterface $request)
    {
        $parts = explode('/', trim($request->getPathInfo(), '/'));
        list(, $urlKey) = array_merge($parts, array_fill(0, 3, null));

        if ($postId = $this->getPostIdByUrlKey($urlKey)) {
            $request
                ->setControllerName('post')
                ->setActionName('view')
                ->setParams(['post_id' => $postId]);
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
}
