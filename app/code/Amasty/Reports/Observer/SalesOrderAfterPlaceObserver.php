<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Observer;

use Amasty\Reports\Model\Abandoned\Cart;
use Amasty\Reports\Model\Abandoned\CartFactory;
use Amasty\Reports\Model\Source\Status;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

/**
 * Class SalesOrderAfterPlaceObserver
 */
class SalesOrderAfterPlaceObserver implements ObserverInterface
{
    /**
     * @var CartFactory
     */
    private $cartFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        CartFactory $cartFactory,
        LoggerInterface $logger
    ) {
        $this->cartFactory = $cartFactory;
        $this->logger = $logger;
    }

    /**
     * @param EventObserver $observer
     *
     * @return void
     *
     * @throws \Exception
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();

        if ($order) {
            try {
                /** @var Cart $abandonedCart */
                $abandonedCart = $this->cartFactory->create();
                $abandonedCart->loadByQuoteId($order->getQuoteId());
                if ($abandonedCart->getId()) {
                    $abandonedCart->setStatus(Status::COMPLETE);
                    $abandonedCart->save();
                }
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
