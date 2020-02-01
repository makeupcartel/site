<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */

namespace Amasty\Ogrid\Model\ResourceModel\Attribute;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Amasty\Ogrid\Model\Attribute', 'Amasty\Ogrid\Model\ResourceModel\Attribute');
    }

    public function joinProductAttributes()
    {
        $this->getSelect()->joinLeft(
            ['product_attributes' => $this->getTable('eav_attribute')],
            'main_table.attribute_id = product_attributes.attribute_id',
            ['frontend_input']
        );

        return $this;
    }
}
