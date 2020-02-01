<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

namespace Amasty\Groupcat\Plugin\Indexer\Product\Save;

use Amasty\Groupcat\Model\Indexer\Product\ProductRuleProcessor;

class ApplyRulesAfterReindex
{
    /**
     * @var ProductRuleProcessor
     */
    protected $productRuleProcessor;

    /**
     * @param ProductRuleProcessor $productRuleProcessor
     */
    public function __construct(ProductRuleProcessor $productRuleProcessor)
    {
        $this->productRuleProcessor = $productRuleProcessor;
    }

    /**
     * Apply groupocat rules after product resource model save
     *
     * @param \Magento\Catalog\Model\Product $subject
     * @param callable $proceed
     * @return \Magento\Catalog\Model\Product
     */
    public function aroundReindex(
        \Magento\Catalog\Model\Product $subject,
        callable $proceed
    ) {
        $proceed();
        $this->productRuleProcessor->reindexRow($subject->getId());
        return;
    }
}
