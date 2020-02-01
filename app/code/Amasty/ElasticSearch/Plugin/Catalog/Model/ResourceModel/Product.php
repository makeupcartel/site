<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Plugin\Catalog\Model\ResourceModel;

use Amasty\ElasticSearch\Model\Indexer\RelevanceRule\ProductRuleProcessor;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\Model\AbstractModel;

class Product
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
     * @param ProductResource $subject
     * @param \Clousure $proceed
     * @param \Magento\Framework\Model\AbstractModel $product
     * @return \Magento\Catalog\Model\ResourceModel\Product
     */
    public function aroundSave(
        ProductResource $subject,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $product
    ) {
        $productResource = $proceed($product);
        if (!$product->getIsMassupdate()) {
            $this->productRuleProcessor->reindexRow($product->getId());
        }

        return $productResource;
    }
}
