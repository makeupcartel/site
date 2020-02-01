<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */

namespace Amasty\Ogrid\Model\Column\Address;

use Magento\Framework\Data\Collection;

class Shipping extends \Amasty\Ogrid\Model\Column
{
    protected $_alias_prefix = 'amasty_ogrid_shipping_';

    protected function _getFieldCondition($mainTableAlias)
    {
        return parent::_getFieldCondition($mainTableAlias) . ' and ' . $this->getAlias() . '.address_type="shipping"';
    }
}