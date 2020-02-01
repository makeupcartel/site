<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Model\ResourceModel;

/**
 * Class WhitelistEntry
 *
 * @package BitExpert\ForceCustomerLogin\Model\ResourceModel
 * @codingStandardsIgnoreFile
 */
class WhitelistEntry extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize connection and define resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bitexpert_forcelogin_whitelist', 'whitelist_entry_id');
    }
}
