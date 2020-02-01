<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Model;

class UserSearch extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Amasty\Xsearch\Model\ResourceModel\UserSearch::class);
    }
}
