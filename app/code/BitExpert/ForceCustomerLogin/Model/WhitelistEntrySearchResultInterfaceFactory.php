<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Model;

/**
 * Class WhitelistEntrySearchResultInterfaceFactory
 *
 * @package BitExpert\ForceCustomerLogin\Api\Data\Collection
 * @codingStandardsIgnoreFile
 */
class WhitelistEntrySearchResultInterfaceFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    private $_instanceName = null;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = '\\BitExpert\\ForceCustomerLogin\\Api\\Data\\Collection\\WhitelistEntrySearchResultInterface'
    ) {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \BitExpert\ForceCustomerLogin\Api\Data\Collection\WhitelistEntrySearchResultInterface
     */
    public function create(array $data = array())
    {
        return $this->_objectManager->create($this->_instanceName, $data);
    }
}
