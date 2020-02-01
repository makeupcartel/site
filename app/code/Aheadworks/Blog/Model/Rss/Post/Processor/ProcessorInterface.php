<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Rss\Post\Processor;

use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\Model\Rss\Post\RssItemInterface;

/**
 * Interface ProcessorInterface
 *
 * @package Aheadworks\Blog\Model\Rss\Post\Processor
 */
interface ProcessorInterface
{
    /**
     * Fill up RSS item with post data
     *
     * @param RssItemInterface $rssItem
     * @param PostInterface $post
     * @return void
     */
    public function process(RssItemInterface $rssItem, PostInterface $post);
}
