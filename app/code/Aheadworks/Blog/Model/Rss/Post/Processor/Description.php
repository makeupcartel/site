<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Rss\Post\Processor;

use Aheadworks\Blog\Model\Rss\Post\RssItemInterface;
use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\Model\Post\FeaturedImageInfo;

/**
 * Class Description
 *
 * @package Aheadworks\Blog\Model\Rss\Post\Processor
 */
class Description implements ProcessorInterface
{
    /**
     * @var FeaturedImageInfo
     */
    private $imageInfo;

    /**
     * @param FeaturedImageInfo $imageInfo
     */
    public function __construct(
        FeaturedImageInfo $imageInfo
    ) {
        $this->imageInfo = $imageInfo;
    }

    /**
     * @inheritdoc
     */
    public function process(RssItemInterface $rssItem, PostInterface $post)
    {
        $description = '
                    <table><tr>
                        <td><a href="%s"><img src="%s" border="0" alt="%s" title="%s" ></a></td>
                        <td  style="text-decoration:none;">%s</td>
                    </tr></table>
                ';

        $description = sprintf(
            $description,
            $rssItem->getLink(),
            $this->imageInfo->getImageUrl($post->getFeaturedImageFile()),
            $post->getFeaturedImageAlt(),
            $post->getFeaturedImageTitle(),
            $post->getShortContent()
        );

        $rssItem->setDescription($description);
    }
}
