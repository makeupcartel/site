<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\ResourceModel;

use Magento\CatalogInventory\Model\ResourceModel\Stock\Status as StockStatusResource;
use Magento\CatalogInventory\Model\Stock;

class OutOfStockIdsProvider
{
    /**
     * @var array
     */
    private $ids = [];

    /**
     * @var StockStatusResource
     */
    private $stockStatusResource;

    public function __construct(
        StockStatusResource $stockStatusResource
    ) {
        $this->stockStatusResource = $stockStatusResource;
    }

    /**
     * @param int $scopeId
     * @return string[]
     */
    public function get($scopeId)
    {
        if (!isset($this->ids[$scopeId])) {
            $connection = $this->stockStatusResource->getConnection();
            $select = $connection->select()->from($this->stockStatusResource->getMainTable())
                ->columns('product_id')
                ->where('website_id = :website_id')
                ->where('stock_id = :stock_id')
                ->where('stock_status = :stock_status')
                ->group('product_id');
            $bind = [
                ':website_id' => $scopeId,
                ':stock_id' => Stock::DEFAULT_STOCK_ID,
                ':stock_status' => Stock::STOCK_OUT_OF_STOCK
            ];
            $outOfStockIds = (array)$connection->fetchCol($select, $bind);
            $this->ids[$scopeId] =  $outOfStockIds;
        }

        return $this->ids[$scopeId];
    }

}
