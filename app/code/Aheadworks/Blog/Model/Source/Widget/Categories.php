<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Source\Widget;

use Magento\Framework\Option\ArrayInterface;
use Aheadworks\Blog\Model\Source\Categories as CategoriesSource;

/**
 * Class Categories
 * @package Aheadworks\Blog\Model\Source\Widget
 */
class Categories extends CategoriesSource implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return parent::toOptionArray();
    }
}
