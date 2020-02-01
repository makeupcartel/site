<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Plugin\Catalog\Model;

use Amasty\ElasticSearch\Model\Indexer\RelevanceRule\ProductRuleProcessor;
use Magento\Catalog\Model\Category;

class CategoryPlugin
{
    /**
     * @var ProductRuleProcessor
     */
    private $productRuleProcessor;

    public function __construct(ProductRuleProcessor $productRuleProcessor)
    {
        $this->productRuleProcessor = $productRuleProcessor;
    }

    /**
     * @param Category $subject
     * @param Category $result
     * @return Category
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(Category $subject, Category $result)
    {
        $productIds = $result->getAffectedProductIds();
        if ($productIds && !$this->productRuleProcessor->isIndexerScheduled()) {
            $this->productRuleProcessor->reindexList($productIds);
        }

        return $result;
    }

    /**
     * @param Category $subject
     * @param Category $result
     * @return Category
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(Category $subject, Category $result)
    {
        $this->productRuleProcessor->markIndexerAsInvalid();
        return $result;
    }
}
