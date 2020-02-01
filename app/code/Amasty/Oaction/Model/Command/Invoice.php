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

class Invoice extends \Amasty\Oaction\Model\Command
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
     * @var InvoiceSender
     */
    private $invoiceSender;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        \Amasty\Oaction\Helper\Data $helper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        Registry $registry,
        InvoiceSender $invoiceCommentSender
    ) {
        parent::__construct();
        $this->helper = $helper;
        $this->objectManager = $objectManager;
        $this->invoiceService = $invoiceService;
        $this->invoiceSender = $invoiceCommentSender;
        $this->registry = $registry;
    }

    /**
     * Executes the command
     *
     * @param AbstractCollection $collection
     * @param int                $notifyCustomer
     *
     * @return string success message if any
     */
    public function execute(AbstractCollection $collection, $notifyCustomer, $oaction)
    {
        $numAffectedOrders = 0;
        $comment = __('Invoice created');
        $capture = $this->helper->getModuleConfig('invoice/capture');

        foreach ($collection as $order) {
            /** @var \Magento\Sales\Model\Order $order */
            $orderCode = $order->getIncrementId();

            try {
                if (!$order->getId()) {
                    throw new \Exception(__('The order no longer exists.'));
                }

                if (!$order->canInvoice()) {
                    throw new \Exception(
                        __('The order does not allow an invoice to be created.')
                    );
                }

                $invoice = $this->invoiceService->prepareInvoice($order);

                if (!$invoice) {
                    throw new \Exception(__('We can\'t save the invoice right now.'));
                }

                if (!$invoice->getTotalQty()) {
                    throw new \Exception(
                        __('You can\'t create an invoice without products.')
                    );
                }

                $invoice->addComment(
                    $comment,
                    $notifyCustomer
                );

                $invoice->setCustomerNote($comment);

                switch ($capture) {
                    case \Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE:
                        $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                        break;
                    case \Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE:
                        $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
                        break;
                }
                $invoice->register();

                $invoice->getOrder()->setCustomerNoteNotify($notifyCustomer);
                $invoice->getOrder()->setIsInProcess(true);

                $transactionSave = $this->objectManager->create(
                    'Magento\Framework\DB\Transaction'
                )->addObject(
                    $invoice
                )->addObject(
                    $invoice->getOrder()
                );
                $transactionSave->save();

                $status = $this->helper->getModuleConfig('invoice/status', $order->getStoreId());
                if ($status) {
                    $order->addStatusToHistory($status, '', $notifyCustomer);
                }

                // send invoice emails
                try {
                    if ($notifyCustomer) {
                        $this->invoiceSender->send($invoice);
                    }
                } catch (\Exception $e) {
                    throw new \Exception(
                        __('We can\'t send the invoice email right now.')
                    );

                }
                $invoiceCode = $invoice->getIncrementId();
                $print = $this->helper->getModuleConfig('invoice/print', $order->getStoreId());
                if ($invoiceCode && $print) {
                    if (!$invoice) {
                        $invoice = $this->objectManager->create('Magento\Sales\Model\Order\Invoice')
                            ->loadByIncrementId($invoiceCode);
                    }
                    $pdfInvoice = $this->objectManager->create('Magento\Sales\Model\Order\Pdf\Invoice');
                    if (!isset($this->_pdf)) {
                        $this->_pdf = $pdfInvoice->getPdf([$invoice]);
                    } else {
                        $tmp = $pdfInvoice->getPdf([$invoice]);
                        $this->_pdf->pages = array_merge($this->_pdf->pages, $tmp->pages);
                    }

                }
                ++$numAffectedOrders;
            } catch (\Exception $e) {
                $err = $e->getMessage();
                $this->_errors[] = __('Can not invoice order #%1: %2', $orderCode, $err);
            }
            $order = null;
            unset($order);
        }

        if ($numAffectedOrders) {
            return $success = __(
                'Total of %1 order(s) have been successfully invoiced.',
                $numAffectedOrders
            );
        }
    }

    public function hasResponse()
    {
        return !empty($this->_pdf);
    }

    public function getResponseName()
    {
        return 'invoices_' . $this->helper->getDate() . '.pdf';
    }

    public function getResponseBody()
    {
        return $this->_pdf->render();
    }
}
