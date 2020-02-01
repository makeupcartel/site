<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model;

/**
 * Class Flag
 *
 * @package Aheadworks\Blog\Model
 */
class Flag extends \Magento\Framework\Flag
{
    /**#@+
     * Constants for blog flags
     */
    const AW_BLOG_SCHEDULE_POST_LAST_EXEC_TIME = 'aw_blog_schedule_post_last_exec_time';
    /**#@-*/

    /**
     * Setter for flag code
     * @codeCoverageIgnore
     *
     * @param string $code
     * @return $this
     */
    public function setBlogFlagCode($code)
    {
        $this->_flagCode = $code;
        return $this;
    }
}
