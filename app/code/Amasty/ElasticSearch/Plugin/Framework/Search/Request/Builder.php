<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Plugin\Framework\Search\Request;

use Magento\Framework\Search\Request\Builder as MagentoRequestBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\CatalogInventory\Model\Stock;
use \Magento\Framework\App\ProductMetadataInterface;
use Magento\Search\Model\AdapterFactory;

class Builder
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var AdapterFactory
     */
    private $adapterFactory;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ProductMetadataInterface $productMetadata,
        AdapterFactory $adapterFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->productMetadata = $productMetadata;
        $this->adapterFactory = $adapterFactory;
    }

    /**
     * @param MagentoRequestBuilder $subject
     * @return array
     */
    public function beforeCreate(MagentoRequestBuilder $subject)
    {
        if ($this->adapterFactory->create() instanceof \Amasty\ElasticSearch\Model\Search\Adapter
            && !$this->scopeConfig->isSetFlag('cataloginventory/options/show_out_of_stock')
        ) {
            $subject->bind($this->getStockStatusAlias(), Stock::STOCK_IN_STOCK);
        }

        return [];
    }

    /**
     * @return string
     */
    private function getStockStatusAlias()
    {
        preg_match('!\d+\.*\d*!', $this->productMetadata->getVersion() ,$match);

        return version_compare($match[0], '2.2', '>')
            ? 'stock_index.stock_status'
            : 'stock_status';
    }
}