<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\App\Router\Matcher;

use Aheadworks\Blog\Api\Data\AuthorInterface;
use Aheadworks\Blog\App\Router\MatcherInterface;
use Aheadworks\Blog\Model\Config;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Aheadworks\Blog\Model\AuthorFactory;

/**
 * Class Author
 * @package Aheadworks\Blog\App\Router\Matcher
 */
class Author implements MatcherInterface
{
    /**
     * @var AuthorFactory
     */
    private $authorFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param AuthorFactory $authorFactory
     * @param Config $config
     */
    public function __construct(AuthorFactory $authorFactory, Config $config)
    {
        $this->authorFactory = $authorFactory;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     * @param RequestInterface|Http $request
     */
    public function match(RequestInterface $request)
    {
        $parts = explode('/', trim($request->getPathInfo(), '/'));
        list(, $urlKey, $authorUrlKey) = array_merge($parts, array_fill(0, 3, null));

        if ($urlKey == $this->config->getRouteToAuthors() && $authorId = $this->getAuthorIdByUrlKey($authorUrlKey)) {
            $request
                ->setControllerName('author')
                ->setActionName('view')
                ->setParams(['author_id' => $authorId]);

            return true;
        }

        return false;
    }

    /**
     * Retrieves author ID by URL-Key
     *
     * @param string $urlKey
     * @return int|null
     */
    private function getAuthorIdByUrlKey($urlKey)
    {
        /** @var \Aheadworks\Blog\Model\Author $authorModel */
        $authorModel = $this->authorFactory->create();
        $authorModel->load($urlKey, AuthorInterface::URL_KEY);
        return $authorModel->getId();
    }
}
