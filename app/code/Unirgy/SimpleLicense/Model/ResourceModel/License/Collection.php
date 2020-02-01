<?php

namespace Unirgy\SimpleLicense\Model\ResourceModel\License;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;


class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Unirgy\SimpleLicense\Model\License', 'Unirgy\SimpleLicense\Model\ResourceModel\License');
    }
}