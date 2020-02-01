<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Tag resource model
 * @package Aheadworks\Blog\Model\ResourceModel
 */
class Tag extends AbstractDb
{
    /**#@+
     * Constants defined for tables
     */
    const BLOG_TAG_TABLE = 'aw_blog_tag';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(self::BLOG_TAG_TABLE, 'id');
    }
}
