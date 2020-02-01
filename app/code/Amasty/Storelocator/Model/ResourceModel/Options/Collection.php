<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model\ResourceModel\Options;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Amasty\Storelocator\Model\Options', 'Amasty\Storelocator\Model\ResourceModel\Options');
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

}
