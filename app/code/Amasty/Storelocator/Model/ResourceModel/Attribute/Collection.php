<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model\ResourceModel\Attribute;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Amasty\Storelocator\Model\Attribute', 'Amasty\Storelocator\Model\ResourceModel\Attribute');
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    public function joinAttributes() {
        $type = '"select", "multiselect", "boolean"';
        $this->getSelect()
            ->joinLeft(
                array('option' => $this->getTable('amasty_amlocator_attribute_option')),
                'main_table.attribute_id = option.attribute_id',
                [
                    'attribute_id' => 'main_table.attribute_id',
                    'options_serialized' => 'option.options_serialized',
                    'value_id' => 'option.value_id'
                ]
            )
            ->where("main_table.frontend_input IN ($type)")
            ->order("option.sort_order")
        ;
        return $this;
    }

    public function getAttributes() {
        $connection = $this->getConnection();
        $select = $this->getSelect();

        return $connection->fetchAll($select);
    }
}
