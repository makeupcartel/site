<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Oaction
 */


namespace Amasty\Oaction\Plugin\Sales\Model\ResourceModel\Order\Handler;

use Amasty\Oaction\Helper\Data as Helper;

class State
{
    /**
     * @var \Amasty\Oaction\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $currentOrder;

    /**
     * State constructor.
     * @param Helper $helper
     */
    public function __construct(Helper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\Handler\State $subject
     * @param \Magento\Sales\Model\Order                             $order
     */
    public function beforeCheck(\Magento\Sales\Model\ResourceModel\Order\Handler\State $subject, $order)
    {
        $this->currentOrder = $order;
    }

    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\Handler\State $subject
     * @param \Magento\Sales\Model\ResourceModel\Order\Handler\State $result
     * @return \Magento\Sales\Model\ResourceModel\Order\Handler\State
     */
    public function afterCheck(\Magento\Sales\Model\ResourceModel\Order\Handler\State $subject, $result)
    {
        /** Check if method was called after mass action */
        if ($this->currentOrder->getIsNeedCheck()) {
            /** If user set custom order status on Ship Action, set this status */
            $shipStatus = $this->helper->getModuleConfig('ship/status', $this->currentOrder->getStoreId());
            if ($shipStatus) {
                $this->currentOrder->addStatusToHistory($shipStatus, '', $this->currentOrder->getIsCustomerNotified());
            }
        }

        return $result;
    }
}
