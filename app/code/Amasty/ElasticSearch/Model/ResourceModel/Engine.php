<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\ResourceModel;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogSearch\Model\ResourceModel\EngineInterface;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;

class Engine implements EngineInterface
{
    /**
     * @var ProductVisibility
     */
    private $productVisibility;

    /**
     * @var IndexScopeResolver
     */
    private $indexScopeResolver;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var OutOfStockIdsProvider
     */
    private $outOfStockIdsProvider;

    /**
     * @var array|bool
     */
    private $outOfStockIdsFlipped = false;

    public function __construct(
        ProductVisibility $productVisibility,
        IndexScopeResolver $indexScopeResolver,
        StockConfigurationInterface $stockConfiguration,
        OutOfStockIdsProvider $outOfStockIdsProvider
    ) {
        $this->productVisibility = $productVisibility;
        $this->indexScopeResolver = $indexScopeResolver;
        $this->stockConfiguration = $stockConfiguration;
        $this->outOfStockIdsProvider = $outOfStockIdsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedVisibility()
    {
        return $this->productVisibility->getVisibleInSiteIds();
    }

    /**
     * {@inheritdoc}
     */
    public function allowAdvancedIndex()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function processAttributeValue($attribute, $value)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareEntityIndex($index, $separator = ' ')
    {
        if (!$this->stockConfiguration->isShowOutOfStock() && !empty($this->getOutOfStockIdsFlipped())) {
            foreach ($index as $attributeId => &$data) {
                if (is_array($data)) {
                    $data = array_diff_key($data, $this->getOutOfStockIdsFlipped());
                    if (empty($data)) {
                        unset($index[$attributeId]);
                    }
                }
            }
        }

        return $index;
    }

    /**
     * @return IndexScopeResolver
     */
    public function isAvailable()
    {
        return true;
    }

    /**
     * @return array
     */
    private function getOutOfStockIdsFlipped()
    {
        if ($this->outOfStockIdsFlipped === false) {
            $scopeId = $this->stockConfiguration->getDefaultScopeId(); //always 0 yet, see MAGETWO-46043
            $this->outOfStockIdsFlipped = array_flip($this->outOfStockIdsProvider->get($scopeId));
        }

        return $this->outOfStockIdsFlipped;
    }
}
