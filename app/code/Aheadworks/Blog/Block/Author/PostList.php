<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Block\Author;

use Aheadworks\Blog\Model\Post;
use Magento\Framework\DataObject\IdentityInterface;
use Aheadworks\Blog\Block\PostList as ParentPostList;

/**
 * List of posts block
 * @package Aheadworks\Blog\Block
 */
class PostList extends ParentPostList implements IdentityInterface
{
    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        $identities = [Post::CACHE_TAG_LISTING];
        foreach ($this->getPosts() as $post) {
            $identities = [Post::CACHE_TAG . '_' . $post->getId()];
        }

        return $identities;
    }
}
