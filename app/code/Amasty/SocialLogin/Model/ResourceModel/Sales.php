<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Sales extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('amasty_sociallogin_sales', 'id');
    }
}
