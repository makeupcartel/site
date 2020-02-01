<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Oaction
 */


namespace Amasty\Oaction\Model\Command;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\DB\TransactionFactory;

class Ship extends \Amasty\Oaction\Model\Command
{
    protected $_pdf = null;

    /**
     * @var \Amasty\Oaction\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader
     */
    private $shipmentLoader;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ShipmentSender
     */
    private $shipmentSender;

    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     * @var \Amasty\Oaction\Model\Source\Carriers
     */
    private $carrier;

    public function __construct(
        \Amasty\Oaction\Helper\Data $helper,
        TransactionFactory $transactionFactory,
        \Magento\Sales\Api\OrderManagementInterface $orderApi,
        \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader,
        \Amasty\Oaction\Model\Source\Carriers $carrier,
        ShipmentSender $shipmentSender,
        Registry $registry
    ) {
        parent::__construct();
        $this->helper = $helper;
        $this->orderApi = $orderApi;
        $this->shipmentLoader = $shipmentLoader;
        $this->registry = $registry;
        $this->shipmentSender = $shipmentSender;
        $this->transactionFactory = $transactionFactory;
        $this->carrier = $carrier;
    }

    /**
     * Executes the command
     * @param AbstractCollection $collection
     * @param bool $notifyCustomer
     * @param array $oaction
     * @return \Magento\Framework\Phrase|string
     */
    public function execute(AbstractCollection $collection, $notifyCustomer, $oaction)
    {
        $numAffectedOrders = 0;
        $comment = __('Shipment created');

        foreach ($collection as $order) {
            /** @var \Magento\Sales\Model\Order $order */
            $orderCode = $order->getIncrementId();
            $orderId = $order->getId();

            try {
                $this->shipmentLoader->setOrderId($orderId);
                $this->shipmentLoader->setShipmentId(null);
                $this->shipmentLoader->setShipment(['comment_text' => $comment]);

                $traking = $this->getTraking($oaction, $orderId);
                $this->shipmentLoader->setTracking($traking);

                $shipment = $this->shipmentLoader->load();
                if (!$shipment) {
                    throw new LocalizedException(__('We can\'t save the shipment right now.'));
                }

                $shipment->addComment(
                    $comment,
                    $notifyCustomer
                );

                $shipment->setCustomerNote($comment);
                $shipment->setCustomerNoteNotify($notifyCustomer);

                $shipment->register();
                $shipment->getOrder()->setCustomerNoteNotify($notifyCustomer);
                $this->_saveShipment($shipment);

                if ($notifyCustomer) {
                    $this->shipmentSender->send($shipment);
                }
                ++$numAffectedOrders;
            } catch (\Exception $e) {
                $err = $e->getMessage();
                $this->_errors[] = __('Can not ship order #%1: %2', $orderCode, $err);
            }
            $order = null;
            unset($order);
            $this->registry->unregister('current_shipment');
        }
        $success = '';
        if ($numAffectedOrders) {
            $success = __(
                'Total of %1 order(s) have been successfully shipped.',
                $numAffectedOrders
            );
        }

        return $success;
    }

    private function getTraking($oaction, $orderId)
    {
        $result = null;
        if (is_array($oaction) && array_key_exists($orderId, $oaction)) {
            $carrierCode = $oaction[$orderId]['amasty-carrier'];
            if ($carrierCode) {
                $title = (array_key_exists('amasty-comment', $oaction[$orderId])
                    && $oaction[$orderId]['amasty-comment'] != '') ?
                    $oaction[$orderId]['amasty-comment'] :
                    $this->getTitleByCode($oaction[$orderId]['amasty-carrier'], null);

                $number = (array_key_exists('amasty-tracking', $oaction[$orderId])) ?
                    $oaction[$orderId]['amasty-tracking']
                    : '';

                $result[] = [
                    'carrier_code' => $carrierCode,
                    'title' => $title,
                    'number' => $number
                ];
            }
        }

        return $result;
    }

    private function getTitleByCode($code, $storeId)
    {
        $title = '';
        if ($code == 'custom') {
            $title = $this->helper->getModuleConfig('ship/title', $storeId);
        } else {
            $carriers = $this->carrier->toOptionArray();
            foreach ($carriers as $carrier) {
                if ($code == $carrier['value']) {
                    $title = $carrier['label'];
                    break;
                }
            }
        }
        if (!$title) {
            $title = $code;
        }

        return $title;
    }

    /**
     * Save shipment and order in one transaction
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return $this
     */
    protected function _saveShipment($shipment)
    {
        $shipment->getOrder()->setIsInProcess(true);
        $transaction = $this->transactionFactory->create();
        $shipment->getOrder()->setIsNeedCheck(true);
        $transaction->addObject(
            $shipment
        )->addObject(
            $shipment->getOrder()
        )->save();

        return $this;
    }
}
