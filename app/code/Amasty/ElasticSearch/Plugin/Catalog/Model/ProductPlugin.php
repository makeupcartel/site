<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Plugin\Catalog\Model;

use Amasty\ElasticSearch\Model\Indexer\RelevanceRule\ProductRuleProcessor;
use Magento\Catalog\Model\Product;


class ProductPlugin
{
    /**
     * @var ProductRuleProcessor
     */
    private $productRuleProcessor;

    /**
     * @param ProductRuleProcessor $productRuleProcessor
     */
    public function __construct(ProductRuleProcessor $productRuleProcessor)
    {
        $this->productRuleProcessor = $productRuleProcessor;
    }

    /**
     * Apply catalog rules after product resource model save
     *
     * @param Product $subject
     * @return void
     */
    public function afterReindex(Product $subject)
    {
        $this->productRuleProcessor->reindexRow($subject->getId());
    }
}
