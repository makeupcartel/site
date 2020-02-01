<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

class Options extends AbstractDb
{

    protected function _construct()
    {
        $this->_init('amasty_amlocator_attribute_option', 'value_id');
    }
}
