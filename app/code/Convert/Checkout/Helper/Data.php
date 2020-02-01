<?php

namespace Convert\Checkout\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * Class Data
 *
 * @package Convert\Checkout\Helper
 */
class Data extends AbstractHelper
{
	
	protected $_storeManager;

    /**
     * @var \Magento\Framework\Url\Helper\Data 
     */
	protected $urlHelper;

    /**
     * @var \Magento\Framework\Registry 
     */
	protected $_registry;

    /**
     * @var \Magento\Customer\Model\Session 
     */
	protected $customerSession;

    /**
     * @var \Magento\Sales\Model\Order 
     */
	protected $_order;
	
    /**
     * @var \Magento\Catalog\Model\CategoryFactory 
     */
	protected $_categoryLoader;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param \Magento\Catalog\Model\CategoryFactory $_categoryLoader
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     */
	public function __construct(
		Context $context,
		\Magento\Catalog\Model\CategoryFactory $_categoryLoader,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Registry $registry,
		\Magento\Sales\Model\Order $order,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\Url\Helper\Data $urlHelper
	)
	{
		parent::__construct($context);
		$this->_order = $order;
		$this->_categoryLoader = $_categoryLoader;
		$this->_storeManager = $storeManager;
		$this->_registry = $registry;
		$this->urlHelper = $urlHelper;
		$this->customerSession = $customerSession;
	}

    /**
     * @param $orderId
     * @return int
     */
	public function checkOrder($orderId)
    {
        $orders = $this->_order->loadByIncrementId($orderId);
        $orderItems = $orders->getAllItems();
        $a = 0;
        foreach ($orderItems as $item) {
            if(isset($item->getProductOptions()['info_buyRequest']['subscription_option'])){
                if ($item->getProductOptions()['info_buyRequest']['subscription_option']['option']) {
                    if ($item->getProductOptions()['info_buyRequest']['subscription_option']['option'] == "subscription") {
                        $a++;
                    }
                }
            }
        }

        if ($a >= 1) {
            return 1;
        } elseif ($a == 0) {
            return 0;
        }
        return 0;
    }

}
