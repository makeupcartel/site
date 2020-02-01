<?php

namespace Unirgy\RapidFlow\Model\ResourceModel;

use Magento\Catalog\Model\Indexer\Product\Price\Action\Full;

class ProductIndexerPrice extends Full
{
    protected $_wdtPrepared;

    public function prepareWebsiteDateTable()
    {
        if (!$this->_wdtPrepared) {
            $this->_prepareWebsiteDateTable();
            $this->_wdtPrepared = true;
        }
        return $this;
    }
}
