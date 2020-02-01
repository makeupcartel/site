<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Observer;

trait CatalogCollectionTrait
{
    /**
     * @param \Magento\Framework\Data\Collection\AbstractDb $collection
     * @param int[]|null $ids
     */
    protected function restrictCollectionIds($collection, $ids)
    {
        if (is_array($ids) && count($ids)) {
            $collection->addFieldToFilter($this->getIdFieldName($collection), ['nin' => $ids]);
        }
    }

    /**
     * @param \Magento\Framework\Data\Collection\AbstractDb $collection
     *
     * @return string
     */
    protected function getIdFieldName($collection)
    {
        if (method_exists($collection, 'getRowIdFieldName')) {
            return $collection->getRowIdFieldName();
        }

        return $collection->getResource()->getIdFieldName();
    }
}
