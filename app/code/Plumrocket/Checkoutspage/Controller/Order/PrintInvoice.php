<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket Checkoutspage v2.x.x
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\Checkoutspage\Controller\Order;

class PrintInvoice extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * Invoice Factory
     * @var \Magento\Sales\Model\Order\InvoiceFactory
     */
    protected $invoiceFactory;

    /**
     * Checkoutspage Helper
     * @var \Plumrocket\Checkoutspage\Helper\Data
     */
    protected $helperData;

    /**
     * @param \Magento\Framework\App\Action\Context      $context           
     * @param \Magento\Framework\Registry                $registry          
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory 
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Sales\Model\Order\InvoiceFactory $invoiceFactory,
        \Plumrocket\Checkoutspage\Helper\Data $helperData
    ) {
        $this->_coreRegistry = $registry;
        $this->_resultPageFactory = $resultPageFactory;
        $this->invoiceFactory = $invoiceFactory;
        $this->helperData = $helperData;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $invoiceId = $this->getRequest()->getParam('invoice_id');
        if ($invoiceId && $this->_canPrintInvoice($invoiceId)) {
            
            $invoice = $this->invoiceFactory->create()->load($invoiceId);
            $order = $invoice->getOrder();

            $this->_coreRegistry->register('current_order', $order);
            $this->_coreRegistry->register('current_invoice', $invoice);

            $this->_view->loadLayout(['default', 'sales_order_printinvoice']);

            $resultPage = $this->_resultPageFactory->create();
            $resultPage->addHandle('print');

            return $resultPage;
        } else {
            $this->_redirect('sales/order/history');
        }
    }

    /**
     * Can print invoice
     * @param  int $invoiceId
     * @return boolean            
     */
    protected function _canPrintInvoice($invoiceId)
    {
        $time = time();
        for ($i = 1; $i <= 7; $i++) {
            if ($this->helperData->getSecretKey($invoiceId, date("Y-m-d", $time)) == $this->getRequest()->getParam('secret')) {
                return true;
            }
            $time = $time - 86400;
        }

        return false;
    }
}
