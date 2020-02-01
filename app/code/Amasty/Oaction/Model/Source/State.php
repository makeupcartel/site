<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Oaction
 */


namespace Amasty\Oaction\Model\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;

/**
 * Class State
 *
 * @package Amasty\Oaction\Model\Source
 */
class State implements ArrayInterface
{
    private $options;

    /**
     * @var CollectionFactory
     */
    private $statusCollectionFactory;

    /**
     * State constructor.
     * @param CollectionFactory $statusCollectionFactory
     */
    public function __construct(
        CollectionFactory $statusCollectionFactory
    ) {
        $this->statusCollectionFactory = $statusCollectionFactory;
    }
    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            /** @var \Magento\Sales\Model\Order\Status[] $statusItems */
            $statusItems = $this->statusCollectionFactory->create()->getItems();
            foreach ($statusItems as $status) {
                $this->options[] = ['value' => $status->getStatus(), 'label' => $status->getLabel()];
            }
        }

        return $this->options;
    }
}
