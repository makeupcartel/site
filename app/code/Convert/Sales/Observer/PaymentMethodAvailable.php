<?php

namespace Convert\Sales\Observer;

use Magento\Framework\Event\ObserverInterface;


class PaymentMethodAvailable implements ObserverInterface
{

    protected $checkoutSession;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * payment_method_is_active event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($observer->getEvent()->getMethodInstance()->getCode() == "afterpay") {
            $checkResult = $observer->getEvent()->getResult();
            $quote = $this->checkoutSession->getQuote();
            foreach ($quote->getAllVisibleItems() as $item) {
                //$productid = $item->getProductId();
                $productOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                $productOption = $productOptions['info_buyRequest']['subscription_option']['option'];
                if ($productOption != 'onetime_purchase') {
                    return false;
                }
            }
            $checkResult->setData('is_available', false);
        }
    }
}
