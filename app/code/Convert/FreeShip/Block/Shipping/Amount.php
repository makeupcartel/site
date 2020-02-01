<?php

namespace Convert\FreeShip\Block\Shipping;

/**
 * Class Amount
 *
 * @package Convert\FreeShip\Block\Shipping
 */
class Amount extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Convert\FreeShip\Helper\Data
     */
	protected $helperData;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
	protected $_cart;

    /**
     * Amount constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Convert\FreeShip\Helper\Data $helperData
     * @param \Magento\Checkout\Model\Cart $cart
     */
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Convert\FreeShip\Helper\Data $helperData,
		\Magento\Checkout\Model\Cart $cart
	)
	{
		$this->helperData = $helperData;
		$this->_cart = $cart;
		parent::__construct($context);
	}

    /**
     * @return mixed
     */
	public function getAmountFreeship()
	{
		return $this->helperData->getGeneralConfig('set_amount');
	}

    /**
     * @return float
     */
	public function getGrandTotal()
	{
		return $this->_cart->getQuote()->getGrandTotal();
	} 
}
