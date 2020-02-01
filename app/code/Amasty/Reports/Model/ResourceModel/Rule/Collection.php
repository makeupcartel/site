<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\ResourceModel\Rule;

use Amasty\Reports\Api\Data\RuleInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Amasty\Reports\Model\ResourceModel\Rule
 */
class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_setIdFieldName(RuleInterface::ENTITY_ID);
        $this->_init(
            \Amasty\Reports\Model\Rule::class,
            \Amasty\Reports\Model\ResourceModel\Rule::class
        );
    }
}
