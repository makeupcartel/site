<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\ResourceModel\Sales\Compare;

/**
 * Class CollectionFactory
 * @package Amasty\Reports\Model\ResourceModel\Sales\Compare
 */
class CollectionFactory implements \Magento\Framework\Data\CollectionDataSourceInterface
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    private $instanceName = null;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = \Amasty\Reports\Model\ResourceModel\Sales\Compare\Collection::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Amasty\Reports\Model\ResourceModel\Sales\Compare\Collection
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create($this->instanceName, $data);
    }
}
