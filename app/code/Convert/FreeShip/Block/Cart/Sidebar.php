<?php

namespace Convert\FreeShip\Block\Cart;


/**
 * Class Sidebar
 *
 * @package Convert\FreeShip\Block\Cart
 */
class Sidebar extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Convert\FreeShip\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Checkout\Model\CartFactory
     */
    protected $_cart;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $_currency;

    /**
     * Sidebar constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Convert\FreeShip\Helper\Data $helperData
     * @param \Magento\Checkout\Model\CartFactory $cart
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Directory\Model\Currency $currency
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Convert\FreeShip\Helper\Data $helperData,
        \Magento\Checkout\Model\CartFactory $cart,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Directory\Model\Currency $currency,
        array $data = []
    )
    {
        $this->helperData = $helperData;
        $this->_cart = $cart;
        $this->_scopeConfig = $scopeConfig;
        $this->_currency = $currency;
        parent::__construct($context, $data);
    }

    /**
     * @param $path
     * @return mixed
     */
    protected function getConfigStoreValue($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function checkconfig()
    {
        $stt = $this->getConfigStoreValue('carriers/freeshipping/active');
        return $stt;
    }

    /**
     * @return int|mixed
     */
    public function getAmountFreeship()
    {
        $amount = $this->getConfigStoreValue('carriers/freeshipping/free_shipping_subtotal');
        if ($amount =='')
        {
            $amount = 0;
        }
        return $amount;
    }

    /**
     * @return mixed
     */
    public function getGrandTotal()
    {
        /** @var \Magento\Checkout\Model\Cart $cart */
        $cart = $this->_cart->create();
        $grandTotal = $cart->getQuote()->getGrandTotal();
        return $grandTotal;
    }

    /**
     * @return string
     */
    public function getCurrentCurrencySymbol()
    {
        return $this->_currency->getCurrencySymbol();
    }
}
