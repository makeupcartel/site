<?php

namespace Convert\CustomerBalance\Controller\Cart;
class Apply extends \Magento\Framework\App\Action\Action
{
    public function execute()
    {
        if (!$this->_objectManager->get(\Magento\CustomerBalance\Helper\Data::class)->isEnabled()) {
            $this->_redirect('customer/account/');
            return;
        }

        $quote = $this->_objectManager->get(\Magento\Checkout\Model\Session::class)->getQuote();
        $this->messageManager->addSuccess(__('Your store credit was successfully applied'));
        $quote->setUseCustomerBalance(true)->collectTotals()->save();

        $this->_redirect('checkout/cart');
    }
}