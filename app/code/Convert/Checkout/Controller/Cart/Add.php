<?php

namespace Convert\Checkout\Controller\Cart;

use Amasty\Cart\Model\Source\Option;
use Amasty\Cart\Model\Source\ConfirmPopup;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Checkout\Helper\Data as HelperData;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Framework\DataObjectFactory as ObjectFactory;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;

/**
 * Class Add
 *
 * @package Convert\Checkout\Controller\Cart
 */
class Add extends \Amasty\Cart\Controller\Cart\Add
{
    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    /**
     * @var \Magento\UrlRewrite\Model\UrlFinderInterface
     */
    private $urlFinder;

    /**
     * URL Model instance
     *
     * @var \Magento\Framework\UrlInterface
     */
    private $_urlInstance;

    /**
     * Add constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param CustomerCart $cart
     * @param ProductRepositoryInterface $productRepository
     * @param \Amasty\Cart\Helper\Data $helper
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param HelperData $helperData
     * @param Escaper $escaper
     * @param UrlHelper $urlHelper
     * @param ObjectFactory $objectFactory
     * @param \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder
     * @param UrlFinderInterface $urlFinder
     * @param \Magento\Framework\UrlInterface $urlInstance
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart,
        ProductRepositoryInterface $productRepository,
        \Amasty\Cart\Helper\Data $helper,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\LayoutInterface $layout,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        HelperData $helperData,
        Escaper $escaper,
        UrlHelper $urlHelper,
        ObjectFactory $objectFactory,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        UrlFinderInterface $urlFinder,
        \Magento\Framework\UrlInterface $urlInstance
    ) {
        $this->objectFactory = $objectFactory;
        $this->urlFinder = $urlFinder;
        $this->_urlInstance = $urlInstance;
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart,
            $productRepository,
            $helper,
            $productHelper,
            $cartHelper,
            $localeResolver,
            $layout,
            $resultPageFactory,
            $coreRegistry,
            $catalogSession,
            $categoryFactory,
            $helperData,
            $escaper,
            $urlHelper,
            $objectFactory,
            $imageBuilder
        );
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $message = __('We can\'t add this item to your shopping cart right now. Please reload the page.');
            return $this->addToCartResponse($message, 0);
        }

        $params = $this->getRequest()->getParams();
        $product = $this->_initProduct();
        if($product->getSubscriptionEnabled() && !isset($params['subscription_option']) && $this->_storeManager->getStore()->getCode() !== 'default'){
            $params['subscription_option']['option'] = 'onetime_purchase';
            $params['subscription_option']['interval'] = '';
            $this->getRequest()->setParams($params);
        }
        return parent::execute();
    }

    /**
     * Creating options popup
     * @param Product $product
     * @param array|null $selectedOptions Selected configurable options
     * @param string|null $submitRoute
     * @return mixed
     */
    protected function showOptionsResponse(Product $product, $selectedOptions = null, $submitRoute = null)
    {
        if($this->_checkoutSession->getSubscriptionProduct()){
            $result = [
                'close_popup'     =>  true
            ];
            $this->_checkoutSession->setSubscriptionProduct(false);
            $resultObject = $this->objectFactory->create(['data' => ['result' => $result]]);
            return $this->getResponse()->representJson(
                $this->helper->encode($resultObject->getResult())
            );
        }
        return parent::showOptionsResponse($product, $selectedOptions, $submitRoute);
    }

    /**
     * @param $message
     * @param $status
     * @param array $additionalResult
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function addToCartResponse($message, $status, $additionalResult = [])
    {
        if($this->_checkoutSession->getSubscriptionProduct()){
            $result = [
                'close_popup'     =>  true
            ];
            $this->_checkoutSession->setSubscriptionProduct(false);
            $resultObject = $this->objectFactory->create(['data' => ['result' => $result]]);
            return $this->getResponse()->representJson(
                $this->helper->encode($resultObject->getResult())
            );
        }

        $result = ['is_add_to_cart' => $status];
        if (!$this->helper->isOpenMinicart()) {
            $cartUrl = $this->cartHelper->getCartUrl();
            if (!$status) {
                $message = '<div class="message error">' . $message . '</div>';
            }
            $result = array_merge(
                $result,
                [
                    'title'     => __('Information'),
                    'message'   => $message,
                    'related'   => $this->getAdditionalBlockHtml(),
                    'b1_name'   => __('Continue'),
                    'b2_name'   => __('View Cart'),
                    'b2_action' => 'document.location = "' . $cartUrl . '";',
                    'b1_action' => 'self.confirmHide();',
                    'checkout'  => '',
                    'timer'     => ''
                ]
            );

            if ($this->helper->isDisplayGoToCheckout()) {
                $goto = __('Go to Checkout');
                $result['checkout'] =
                    '<a class="checkout"
                    title="' . $goto . '"
                    data-role="proceed-to-checkout"
                    href="' . $this->helper->getUrl('checkout') . '"
                    >
                    ' . $goto . '
                </a>';
            }

            $isProductView = $this->getRequest()->getParam('product_page');
            if ($isProductView == 'true' && $this->helper->getProductButton()) {
                $categoryId = $this->catalogSession->getLastVisitedCategoryId();
                if (!$categoryId && $this->getProduct()) {
                    $productCategories = $this->getProduct()->getCategoryIds();
                    if (count($productCategories) > 0) {
                        $categoryId = $productCategories[0];
                        if ($categoryId == $this->_storeManager->getStore()->getRootCategoryId()) {
                            if (isset($productCategories[1])) {
                                $categoryId = $productCategories[1];
                            } else {
                                $categoryId = null;
                            }
                        }
                    }
                }
                if ($categoryId) {
                    $rewrite = $this->urlFinder->findOneByData([
                        UrlRewrite::ENTITY_ID => $categoryId,
                        UrlRewrite::ENTITY_TYPE => CategoryUrlRewriteGenerator::ENTITY_TYPE,
                        UrlRewrite::STORE_ID => $this->_storeManager->getStore()->getId(),
                    ]);
                    if ($rewrite) {
                        $result['b1_action'] = 'document.location = "' .
                            $this->getUrlInstance()->getDirectUrl($rewrite->getRequestPath())
                            . '";';
                    }else{
                        $category = $this->categoryFactory->create()->load($categoryId);
                        if ($category) {
                            $result['b1_action'] = 'document.location = "' .
                                $category->getUrl()
                                . '";';
                        }
                    }
                    
                }

            }

            //add timer
            $time = $this->helper->getTime();
            if (0 < $time) {
                $result['timer'] .= '<span class="timer">' . '(' . $time . ')' . '</span>';
            }
        } else {
            $this->messageManager->addSuccessMessage(
                __('%1 has been added to your cart.', $this->getProduct()->getName())
            );
        }
        $result = array_merge($result, $additionalResult);

        if ($status) {
            $result['product_sku'] = $this->getProduct()->getSku();
            $result['product_id'] = $this->getProduct()->getId();
        }

        $resultObject = $this->objectFactory->create(['data' => ['result' => $result]]);
        $this->_eventManager->dispatch(
            'amasty_cart_add_addtocart_response_after',
            ['controller' => $this, 'result' => $resultObject]
        );

        return $this->getResponse()->representJson(
            $this->helper->encode($resultObject->getResult())
        );
    }

    /**
     * Retrieve URL instance
     *
     * @return \Magento\Framework\UrlInterface
     */
    private function getUrlInstance()
    {
        return $this->_urlInstance;
    }

    /**
     * @param Product $product
     * @param $message
     * @return string
     */
    protected function getProductAddedMessage(Product $product, $message)
    {
        if ($this->helper->isDisplayImageBlock()) {
            $block = $this->layout->getBlock('amasty.cart.product');
            if (!$block) {
                $block = $this->layout->createBlock(
                    \Amasty\Cart\Block\Product::class,
                    'amasty.cart.product',
                    [ 'data' => [] ]
                );
                $block->setTemplate('Amasty_Cart::dialog.phtml');
            }
            
            $subscriptionOption = $this->getRequest()->getParam('subscription_option');
            $html = '';
            if($subscriptionOption && $subscriptionOption['option'] == 'subscription'){
                $html = '<div class="subscription-option"><strong>Regular Delivery:</strong> <span>' . $subscriptionOption['interval'] . '</span></div>';
            }
            $block->setSubscriptionOption($html);
        }
        return parent::getProductAddedMessage($product, $message);
    }
}