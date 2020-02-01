<?php

namespace Convert\Checkout\Plugin;

/**
 * Class CreationInfo
 *
 * @package Convert\Checkout\Plugin
 */
class CreationInfo extends \Magento\NegotiableQuote\Block\Order\Info\CreationInfo
{

	protected $orderId = 'order_id';

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
	protected $_orderRepository;

    /**
     * CreationInfo constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Api\CustomerNameGenerationInterface $customerViewHelper
     * @param array $data
     */
	public function __construct(
	    \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\CustomerNameGenerationInterface $customerViewHelper,
        array $data = []
    )
    {
        parent::__construct($context, $orderRepository, $customerRepository, $customerViewHelper, $data);
        $this->_orderRepository = $orderRepository;
    }

    /**
     * @return string
     */
    public function afterGetCreationInfo()
	{
		$creationInfo = '';
		$orderId = (int)$this->getRequest()->getParam($this->orderId);
		if ($orderId) {
			$order = $this->_orderRepository->get($orderId);
			if ($order && $order->getEntityId()) {
				$creationInfo = $this->getOrderCreatedAtInfo($order);
			}
		}
		return $creationInfo;
	}

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return string
     */
	public function getOrderCreatedAtInfo(\Magento\Sales\Api\Data\OrderInterface $order)
	{
		return $this->formatDate($order->getCreatedAt(), \IntlDateFormatter::LONG);
	}

}
