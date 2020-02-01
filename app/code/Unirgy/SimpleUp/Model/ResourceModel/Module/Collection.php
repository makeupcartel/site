<?php

namespace Unirgy\SimpleUp\Model\ResourceModel\Module;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;


class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Unirgy\SimpleUp\Model\Module', 'Unirgy\SimpleUp\Model\ResourceModel\Module');
    }
}