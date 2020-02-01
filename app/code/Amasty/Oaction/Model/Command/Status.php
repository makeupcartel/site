<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Oaction
 */


namespace Amasty\Oaction\Model\Command;

use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Registry;
use Magento\Sales\Model\ResourceModel\GridPool;

class Status extends \Amasty\Oaction\Model\Command
{
    /**
     * @var \Amasty\Oaction\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected $_pdf = null;

    /**
     * @var InvoiceService
     */
    private $invoiceService;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender
     */
    private $commentSender;

    /**
     * @var GridPool
     */
    private $gridPool;

    public function __construct(
        \Amasty\Oaction\Helper\Data $helper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Sales\Api\OrderManagementInterface $orderApi,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender $commentSender,
        Registry $registry,
        InvoiceSender $invoiceCommentSender,
        GridPool $gridPool
    ) {
        parent::__construct();
        $this->helper = $helper;
        $this->objectManager = $objectManager;
        $this->orderApi = $orderApi;
        $this->invoiceService = $invoiceService;
        $this->invoiceCommentSender = $invoiceCommentSender;
        $this->registry = $registry;
        $this->commentSender = $commentSender;
        $this->gridPool = $gridPool;
    }

    /**
     * Executes the command
     *
     * @param AbstractCollection $collection
     * @param int $notify
     * @return string success message if any
     */
    public function execute(AbstractCollection $collection, $status, $oaction)
    {
        $numAffectedOrders = 0;

        $comment = __('Status changed');

        foreach ($collection as $order) {
            /** @var \Magento\Sales\Model\Order $order */
            $orderCode = $order->getIncrementId();

            try {
                if ($this->helper->getModuleConfig('status/check_state')) {
                    $state = $order->getState();
                    $statuses = $this->objectManager->create('Magento\Sales\Model\Order\Status')
                        ->getCollection()
                        ->addStateFilter($state)
                        ->toOptionHash();

                    if (!array_key_exists($status, $statuses)) {
                        $err = __('Selected status does not correspond to the state of order.');
                        $this->_errors[] = __('Can not update order #%1: %2', $orderCode, $err);
                        continue;
                    }
                }

                $history = $order->addStatusHistoryComment($comment, $status);
                $history->setIsVisibleOnFront(false);
                $history->setIsCustomerNotified(false);
                $history->save();

                //compatibility for order status extention
                if ($history->getIsCustomerNotified() === true) {
                    $this->commentSender->send($order, $history->getIsCustomerNotified(), '');
                }

                $order->save();

                ++$numAffectedOrders;

                $this->gridPool->refreshByOrderId($order->getId());
            } catch (\Exception $e) {
                $err = $e->getMessage();
                $this->_errors[] = __('Can not update order #%1: %2', $orderCode, $err);
            }

            unset($order);
        }

        $success = ($numAffectedOrders)
            ? $success = __('Total of %1 order(s) have been successfully updated.', $numAffectedOrders)
            : '';

        return $success;
    }
}
