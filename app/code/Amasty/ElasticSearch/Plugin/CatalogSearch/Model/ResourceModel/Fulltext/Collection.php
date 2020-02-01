<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Plugin\CatalogSearch\Model\ResourceModel\Fulltext;

use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection as FulltextCollection;
use Magento\Catalog\Model\Product\ProductList\Toolbar as ToolbarModel;
use Magento\Framework\App\ProductMetadataInterface;

class Collection
{

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var ToolbarModel
     */
    private $toolbarModel;

    public function __construct(ProductMetadataInterface $productMetadata, ToolbarModel $toolbarModel)
    {
        $this->productMetadata = $productMetadata;
        $this->toolbarModel = $toolbarModel;
    }

    /**
     * @param FulltextCollection $collection
     * @param bool $printQuery
     * @param bool $logQuery
     * @return array
     */
    public function beforeLoad(FulltextCollection $collection, $printQuery = false, $logQuery = false)
    {
        if (!$collection->isLoaded() && $this->isLessThan22()) {
            $this->setRelevanceOrder($collection);
        }

        return [$printQuery, $logQuery];
    }

    /**
     * @param FulltextCollection $collection
     */
    public function beforeGetSize(FulltextCollection $collection)
    {
        if (!$collection->isLoaded() && $this->isLessThan22()) {
            $this->setRelevanceOrder($collection);
        }
    }

    private function isLessThan22()
    {
        return version_compare($this->productMetadata->getVersion(), '2.2', '<');
    }

    /**
     * @param FulltextCollection $collection
     * @return FulltextCollection
     */
    private function setRelevanceOrder(FulltextCollection $collection)
    {
        $direction = strtolower($this->toolbarModel->getDirection());
        if ($direction) {
            $collection->setOrder('relevance', $direction);
        } else {
            $collection->setOrder('relevance');
        }

        return $collection;
    }
}
