<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Rss\Post\Processor;

use Aheadworks\Blog\Model\Rss\Post\RssItemInterface;
use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\Model\Url as BlogUrl;

/**
 * Class Link
 *
 * @package Aheadworks\Blog\Model\Rss\Post\Processor
 */
class Link implements ProcessorInterface
{
    /**
     * @var BlogUrl
     */
    protected $blogUrl;

    /**
     * @param BlogUrl $blogUrl
     */
    public function __construct(
        BlogUrl $blogUrl
    ) {
        $this->blogUrl = $blogUrl;
    }

    /**
     * @inheritdoc
     */
    public function process(RssItemInterface $rssItem, PostInterface $post)
    {
        $rssItem->setLink($this->blogUrl->getPostUrl($post));
    }
}
