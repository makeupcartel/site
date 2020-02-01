<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */

namespace Amasty\Ogrid\Plugin;

class OrderItem
{
    protected $_productAttributesIndexerProcessor;

    public function __construct(
        \Amasty\Ogrid\Model\Indexer\Attribute\Processor $productAttributesIndexerProcessor
    ){
        $this->_productAttributesIndexerProcessor = $productAttributesIndexerProcessor;
    }

    public function afterAfterSave(
        \Magento\Sales\Model\Order\Item $item,
        $result
    ){
        $this->_productAttributesIndexerProcessor->reindexRow($item->getId());
        return $result;
    }
}