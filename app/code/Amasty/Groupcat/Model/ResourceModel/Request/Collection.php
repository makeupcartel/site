<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Model\ResourceModel\Request;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amasty\Groupcat\Model\Request', 'Amasty\Groupcat\Model\ResourceModel\Request');
    }

    public function deleteByIds($ids)
    {
        $this->getConnection()->delete(
            $this->getMainTable(),
            ['request_id IN(?)' => implode(',', $ids)]
        );
    }
}
