<?php

namespace Convert\AfterpayRestriction\Block\Catalog;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\Product as Product;
use Magento\Framework\Registry as Registry;
use Magento\Directory\Model\Currency as Currency;
use Afterpay\Afterpay\Model\Config\Payovertime as AfterpayConfig;
use Afterpay\Afterpay\Model\Payovertime as AfterpayPayovertime;
use Magento\Framework\Component\ComponentRegistrar as ComponentRegistrar;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Installments
 *
 * @package Convert\AfterpayRestriction\Block\Catalog
 */
class Installments extends \Afterpay\Afterpay\Block\Catalog\Installments
{
    const GIFT_CARD = 'giftcard';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Installments constructor.
     *
     * @param Template\Context $context
     * @param Product $product
     * @param Registry $registry
     * @param Currency $currency
     * @param AfterpayConfig $afterpayConfig
     * @param AfterpayPayovertime $afterpayPayovertime
     * @param ComponentRegistrar $componentRegistrar
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Product $product,
        Registry $registry,
        Currency $currency,
        AfterpayConfig $afterpayConfig,
        AfterpayPayovertime $afterpayPayovertime,
        ComponentRegistrar $componentRegistrar,
        StoreManagerInterface $storeManager,
        array $data
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct(
            $context,
            $product,
            $registry,
            $currency,
            $afterpayConfig,
            $afterpayPayovertime,
            $componentRegistrar,
            $data
        );
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getPaymentIsActive()
    {
        // get current product
        $product = $this->registry->registry('product');
        if(self::GIFT_CARD === $product->getTypeId() ||
           $product->getResource()->getAttributeRawValue(
                $product->getId(),
                'available_afterpay',
                $this->_storeManager->getStore()->getId()
            )
        ){
            return false;
        }
        return $this->afterpayConfig->isActive();
    }
}
