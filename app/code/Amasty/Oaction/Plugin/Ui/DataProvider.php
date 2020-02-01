<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Oaction
 */


namespace Amasty\Oaction\Plugin\Ui;

class DataProvider
{
    /**
     * @var \Amasty\Oaction\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Shipping\Model\CarrierFactory
     */
    protected $_carrierFactory;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection
     */
    private $trackCollectionFactory;

    public function __construct(
        \Amasty\Oaction\Helper\Data $helper,
        \Magento\Shipping\Model\CarrierFactory $carrierFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory $trackCollectionFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->_helper                = $helper;
        $this->_carrierFactory        = $carrierFactory;
        $this->orderRepository        = $orderRepository;
        $this->trackCollectionFactory = $trackCollectionFactory;
    }

    /**
     * Create xml config with php for enable\disable it from admin panel
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider $subject
     * @param array                                                                 $result
     *
     * @return array
     */
    public function afterGetData(
        \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider $subject,
        $result
    ) {
        if ($subject->getName() == 'sales_order_grid_data_source' && array_key_exists('items', $result)) {
            foreach ($result['items'] as $id => $item) {
                $orderId = $item['entity_id'];
                /** @var \Magento\Sales\Model\Order $order */
                $order   = $this->orderRepository->get($orderId);

                $canShip = $order->canShip();
                $result['items'][$id]['can_ship'] = $canShip;
                if ($canShip) {
                    continue;
                }

                /** @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection $collection */
                $collection = $this->trackCollectionFactory->create()
                    ->addAttributeToSelect('track_number')
                    ->addAttributeToSelect('title')
                    ->setOrderFilter($orderId);

                $numbers  = [];
                $carriers = [];
                foreach ($collection as $track) {
                    $numbers[]  = $track->getData('track_number');
                    $carriers[] = $track->getTitle();
                }
                $result['items'][$id]['has_shipments']    = $order->hasShipments();
                $result['items'][$id]['track_exist']      = (int)!empty($carriers);
                $result['items'][$id]['is_canceled']      = (int)$order->isCanceled();
                $result['items'][$id]['current_tracking'] = implode(', ', $numbers);
                $result['items'][$id]['current_carrier']  = implode(', ', $carriers);
            }
        }

        return $result;
    }
}
