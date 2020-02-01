<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

namespace Amasty\Groupcat\Plugin\Indexer;

use Amasty\Groupcat\Model\Indexer\Product\ProductRuleProcessor;

class Category extends \Magento\CatalogRule\Plugin\Indexer\Category
{
    /**
     * override constructor. Indexer is changed
     *
     * @param ProductRuleProcessor $productRuleProcessor
     */
    public function __construct(
        ProductRuleProcessor $productRuleProcessor
    ) {
        $this->productRuleProcessor = $productRuleProcessor;
    }
}
