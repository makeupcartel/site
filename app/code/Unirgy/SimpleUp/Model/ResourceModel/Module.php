<?php

namespace Unirgy\SimpleUp\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;


class Module extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('usimpleup_module', 'module_id');
    }
}