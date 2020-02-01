<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Convert\OldOrder\Block\Order;

use \Magento\Framework\App\ObjectManager;
use \Convert\OldOrder\Model\ResourceModel\OldOrder\CollectionFactoryInterface;

/**
 * Sales order history block
 *
 * @api
 * @since 100.0.2
 */
class History extends \Magento\Framework\View\Element\Template
{


    /**
     * @var \Convert\OldOrder\Model\ResourceModel\OldOrder\CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Convert\OldOrder\Model\Order\Config
     */
    protected $_orderConfig;

    /**
     * @var \Convert\OldOrder\Model\ResourceModel\OldOrder\Collection
     */
    protected $orders;

    /**
     * @var CollectionFactoryInterface
     */
    private $orderCollectionFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Convert\OldOrder\Model\ResourceModel\OldOrder\CollectionFactory $orderCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager,
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Convert\OldOrder\Model\ResourceModel\OldOrder\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->_storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;

        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
    }

    /**
     * @return CollectionFactoryInterface
     *
     * @deprecated 100.1.1
     */
    private function getOrderCollectionFactory()
    {
        if ($this->orderCollectionFactory === null) {
            $this->orderCollectionFactory = ObjectManager::getInstance()->get(CollectionFactoryInterface::class);
        }
        return $this->orderCollectionFactory;
    }

    /**
     * @return bool|\Convert\OldOrder\Model\ResourceModel\OldOrder\Collection
     */
    public function getOldOrders()
    {   
        $customerEmail = $this->_customerSession->getCustomer()->getEmail();
        $storeId = $this->_storeManager->getStore()->getId();
        if (!($customerEmail)) {
            return false;
        }
        if (!$this->orders) {
            $this->orders = $this->getOrderCollectionFactory()->create($customerEmail,$storeId)->addFieldToSelect(
                '*'
            )->setOrder(
                'ordered_date',
                'desc'
            );
        }
        return $this->orders;
    }
    /**
     * Function formatPrice
     *
     * @param float $price
     *
     * @return string
     */
    public function formatPrice($amount)
    {
        return $this->priceCurrency->convertAndFormat($amount);
    }
}
