<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Indexer\Structure\EntityBuilder\Product;

use Amasty\ElasticSearch\Api\Data\Indexer\Structure\EntityBuilderInterface;
use Amasty\ElasticSearch\Model\Indexer\Structure\EntityBuilder\Product;

class StockStatus implements EntityBuilderInterface
{
    const STOCK_STATUS = 'stock_status';

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function buildEntityFields()
    {
        return [self::STOCK_STATUS => ['type' => Product::ATTRIBUTE_TYPE_INT]];
    }
}
