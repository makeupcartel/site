<?php

namespace Convert\Checkout\Controller\Cart;

use Magento\Catalog\Controller\Product\View\ViewInterface;
use Magento\Checkout\Model\Cart as CustomerCart;

/**
 * Class UpdatePost
 *
 * @package Convert\Checkout\Controller\Cart
 */
class UpdatePost extends \Magento\Checkout\Controller\Cart\UpdatePost
{
    protected $jsonData = [];
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * UpdatePost constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param CustomerCart $cart
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        CustomerCart $cart
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $scopeConfig, $checkoutSession, $storeManager, $formKeyValidator, $cart);
    }

    /**
     * Update customer's shopping cart
     *
     * @return void
     */
    protected function _updateShoppingCart()
    {
        $cartData = $this->getRequest()->getParam('cart');
        if(is_array($cartData))
        {
            if(!(count($cartData) === 1 && isset($cartData[0]) && !$cartData[0])){
                parent::_updateShoppingCart();
            }
        }
    }

    /**
     * @param null $backUrl
     * @return \Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _goBack($backUrl = null)
    {
        /** @var $store \Magento\Store\Model\Store */
        $store = $this->_storeManager->getStore();
        if($store->getCode() === 'default'){
            $result = $this->resultJsonFactory->create();
            /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $layout = $resultPage->addHandle('checkout_cart_index')->getLayout();
            /** @var Template $block */
            $this->jsonData['cart'] = $layout->getBlock('checkout.cart')->toHtml();
            $promoAddBlock = $layout->getBlock('ampromo.add');
            $this->jsonData['sourceUrl'] = $promoAddBlock->getUrl('amasty_promo/popup/reload');
            $this->jsonData['uenc'] = $promoAddBlock->getCurrentBase64Url();
            $this->jsonData['commonQty'] = $promoAddBlock->getAvailableProductQty()['common_qty'];
            $this->jsonData['products'] = \Zend_Json::encode($promoAddBlock->getAvailableProductQty());
            $this->jsonData['formUrl'] = $promoAddBlock->getFormActionUrl();
            $this->jsonData['selectionMethod'] = $promoAddBlock->getSelectionMethod();
            $this->jsonData['giftsCounter'] = $promoAddBlock->getGiftsCounter();
            $this->jsonData['autoOpenPopup'] = $promoAddBlock->isOpenAutomatically() ? "true" : "false";
            $this->jsonData['reward_point'] = $layout->getBlock('reward.tooltip.checkout')->toHtml();
            $result->setData($this->jsonData);
            return $result;
        }else{
            return parent::_goBack($backUrl);
        }
    }
}