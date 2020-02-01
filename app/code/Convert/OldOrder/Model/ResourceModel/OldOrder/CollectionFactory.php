<?php

namespace Convert\OldOrder\Model\ResourceModel\OldOrder;

/**
 * Class CollectionFactory
 */
class CollectionFactory
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

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = \Convert\OldOrder\Model\ResourceModel\OldOrder\Collection::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * {@inheritdoc}
     */
    public function create($customerEmail = null,$storeViewID = null)
    {
        /** @var \Convert\OldOrder\Model\ResourceModel\OldOrder\Collection $collection */
        $collection = $this->objectManager->create($this->instanceName);

        if ($customerEmail) {
            $collection->addFieldToFilter('email_address', $customerEmail);
            $collection->addFieldToFilter('store_id',$storeViewID);
        }

        return $collection;
    }
}
