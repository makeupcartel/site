<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\App\Router\Matcher;

use Aheadworks\Blog\Api\Data\TagInterface;
use Aheadworks\Blog\App\Router\MatcherInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Aheadworks\Blog\Model\TagFactory;

/**
 * Class Tag
 * @package Aheadworks\Blog\App\Router\Matcher
 */
class Tag implements MatcherInterface
{
    /**
     * @var string
     */
    const TAG_KEY = 'tag';

    /**
     * @var TagFactory
     */
    private $tagFactory;

    /**
     * @param TagFactory $tagFactory
     */
    public function __construct(TagFactory $tagFactory)
    {
        $this->tagFactory = $tagFactory;
    }

    /**
     * {@inheritdoc}
     * @param RequestInterface|Http $request
     */
    public function match(RequestInterface $request)
    {
        $parts = explode('/', trim($request->getPathInfo(), '/'));
        list(, $urlKey, $tagName) = array_merge($parts, array_fill(0, 3, null));

        if ($urlKey == self::TAG_KEY && $tagName) {
            $request
                ->setControllerName('index')
                ->setActionName('index')
                ->setParams(['tag_id' => $this->getTagIdByName(urldecode($tagName))]);

            return true;
        }

        return false;
    }

    /**
     * Retrieves tag ID by name
     *
     * @param string $tagName
     * @return int|null
     */
    private function getTagIdByName($tagName)
    {
        /** @var \Aheadworks\Blog\Model\Tag $tagModel */
        $tagModel = $this->tagFactory->create();
        $tagModel->load($tagName, TagInterface::NAME);
        return $tagModel->getId();
    }
}
