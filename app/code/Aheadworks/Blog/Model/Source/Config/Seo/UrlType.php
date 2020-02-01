<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Source\Config\Seo;

/**
 * Class UrlType
 * @package Aheadworks\Blog\Model\Source\Config\Seo
 */
class UrlType implements \Magento\Framework\Option\ArrayInterface
{
    /**#@+
     * Constants defined for url types
     */
    const URL_EXC_CATEGORY = 'url_exclude_category';
    const URL_INC_CATEGORY = 'url_include_category';
    /**#@-*/

    /**
     * Retrieve url types as option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::URL_EXC_CATEGORY, 'label' => __('site.com/blog/article')],
            ['value' => self::URL_INC_CATEGORY, 'label' => __('site.com/blog/category/article')],
        ];
    }
}
