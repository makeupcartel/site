<?php

namespace Convert\Sales\Plugin\Model;

use \Magento\Payment\Model\InfoInterface;
use \Magento\Framework\Exception\LocalizedException as LocalizedException;
use \Afterpay\Afterpay\Helper\Data as Helper;
use \Magento\Quote\Model\ResourceModel\Quote\Payment as PaymentQuoteRepository;

class Payovertime
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
        \Magento\Framework\Exception\LocalizedExceptionFactory $exception,
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    public function afterIsAvailable(\Afterpay\Afterpay\Model\Payovertime $subject, $result)
    {
        if($result){
            $flag = true;
            $quote = $this->checkoutSession->getQuote();
            foreach ($quote->getAllVisibleItems() as $item) {
                //$productid = $item->getProductId();
                $productOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                if(isset($productOptions['info_buyRequest']['subscription_option'])){
                    $productOption = $productOptions['info_buyRequest']['subscription_option']['option'];
                    if($productOption == 'onetime_purchase'){
                        $flag = true;
                    }else{
                        $flag = false;
                        return $flag;
                    }
                }else{
                    $flag = true;
                }
            }
            return $flag;
        }
        return $result;
    }
}
