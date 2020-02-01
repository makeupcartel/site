<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\ResourceModel\Abandoned\Cart;

use Amasty\Reports\Model\Abandoned\Cart as Model;
use Amasty\Reports\Model\ResourceModel\Abandoned\Cart as ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = ResourceModel::ID;

    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            Model::class,
            ResourceModel::class
        );
    }
}
