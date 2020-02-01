<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Model\ResourceModel\WhitelistEntry;

/**
 * Class Collection
 *
 * @package BitExpert\ForceCustomerLogin\Model\ResourceModel\WhitelistEntry
 * @codingStandardsIgnoreFile
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'whitelist_entry_id';

    /**
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init(
            'BitExpert\ForceCustomerLogin\Model\WhitelistEntry',
            'BitExpert\ForceCustomerLogin\Model\ResourceModel\WhitelistEntry'
        );
    }
}
