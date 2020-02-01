<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Rss\Post\Processor;

use Aheadworks\Blog\Model\Rss\Post\RssItemInterface;
use Aheadworks\Blog\Api\Data\PostInterface;

/**
 * Class Common
 *
 * @package Aheadworks\Blog\Model\Rss\Post\Processor
 */
class Common implements ProcessorInterface
{
    /**
     * @inheritdoc
     */
    public function process(RssItemInterface $rssItem, PostInterface $post)
    {
        $rssItem->setTitle($post->getTitle());
        $rssItem->setDateCreated(strtotime($post->getPublishDate()));
    }
}
