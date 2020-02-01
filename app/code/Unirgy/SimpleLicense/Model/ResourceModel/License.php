<?php

namespace Unirgy\SimpleLicense\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;


class License extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('usimplelic_license', 'license_id');
    }
}