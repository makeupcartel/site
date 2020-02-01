<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\ResourceModel;

use Aheadworks\Blog\Api\Data\AuthorInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Author
 * @package Aheadworks\Blog\Model\ResourceModel
 */
class Author extends AbstractDb
{
    /**#@+
     * Constants defined for table names
     */
    const BLOG_AUTHOR_TABLE = 'aw_blog_author';
    const BLOG_AUTHOR_POST_TABLE = 'aw_blog_author_post';
    /**#@-*/

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::BLOG_AUTHOR_TABLE, AuthorInterface::ID);
    }
}
