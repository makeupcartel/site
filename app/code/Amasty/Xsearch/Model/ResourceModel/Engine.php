<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amasty\Xsearch\Model\ResourceModel;

class Engine extends \Magento\CatalogSearch\Model\ResourceModel\Engine
{
    public function processAttributeValue($attribute, $value)
    {
        $result = false;
        if (in_array($attribute->getFrontendInput(), ['text', 'textarea'])
        ) {
            $result = $value;
        }

        return $result;
    }
}
