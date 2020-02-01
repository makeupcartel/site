<?php

namespace Unirgy\RapidFlow\Model\ResourceModel\CatalogRule;

use Magento\CatalogRule\Model\ResourceModel\Rule\Collection as RuleCollection;

class Collection extends RuleCollection
{
    protected function _construct()
    {
        $this->_init('Unirgy\RapidFlow\Model\CatalogRule', 'Unirgy\RapidFlow\Model\ResourceModel\CatalogRule');
    }

    public function addIsActiveFilter($filterNow = false)
    {
        if ($filterNow) {
            $this->getSelect()->where('from_date<=?', \Unirgy\RapidFlow\Helper\Data::now(true));
        }
        $this->getSelect()->where('to_date>=? or to_date is null', \Unirgy\RapidFlow\Helper\Data::now(true));
        $this->getSelect()->where('is_active=1');
        return $this;
    }
}
