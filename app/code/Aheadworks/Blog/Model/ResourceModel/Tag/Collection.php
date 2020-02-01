<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\ResourceModel\Tag;

use Aheadworks\Blog\Model\Tag;
use Aheadworks\Blog\Model\ResourceModel\Tag as ResourceTag;

/**
 * Class Collection
 * @package Aheadworks\Blog\Model\ResourceModel\Tag
 */
class Collection extends \Aheadworks\Blog\Model\ResourceModel\AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(Tag::class, ResourceTag::class);
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('id', 'name');
    }
}
