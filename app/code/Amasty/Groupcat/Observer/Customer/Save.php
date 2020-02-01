<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Observer\Customer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class Save implements ObserverInterface
{
    /**
     * @var \Amasty\Groupcat\Model\Indexer\Customer\IndexBuilder
     */
    private $indexBuilder;

    /**
     * Save constructor.
     * @param \Amasty\Groupcat\Model\Indexer\Customer\IndexBuilder $indexBuilder
     */
    public function __construct(
        \Amasty\Groupcat\Model\Indexer\Customer\IndexBuilder $indexBuilder
    ) {
        $this->indexBuilder = $indexBuilder;
    }

    /**
     * reindex after customer update
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $customerId = $observer->getCustomerDataObject()->getId();
        $this->indexBuilder->reindexByCustomerId($customerId);
    }
}
