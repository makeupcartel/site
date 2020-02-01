<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */
namespace Amasty\Cart\Controller\Cart;

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

class Add extends \Magento\Checkout\Controller\Cart\Add
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var \Amasty\Cart\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_productHelper;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\App\ViewInterface
     */
    protected $_view;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    /**
     * @var \Magento\Catalog\Model\Session
     */
    protected $catalogSession;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $cartHelper;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    /**
     * @var array
     */
    private $messages = [];

    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder
     */
    private $imageBuilder;

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
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart,
            $productRepository
        );

        $this->helper = $helper;
        $this->_productHelper = $productHelper;
        $this->helperData = $helperData;
        $this->resultPageFactory = $resultPageFactory;
        $this->_view = $context->getView();
        $this->_coreRegistry = $coreRegistry;
        $this->urlHelper = $urlHelper;
        $this->catalogSession = $catalogSession;
        $this->categoryFactory = $categoryFactory;
        $this->layout = $layout;
        $this->escaper = $escaper;
        $this->cartHelper = $cartHelper;
        $this->localeResolver = $localeResolver;
        $this->objectFactory = $objectFactory;
        $this->imageBuilder = $imageBuilder;
    }

    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $message = __('We can\'t add this item to your shopping cart right now. Please reload the page.');
            return $this->addToCartResponse($message, 0);
        }

        $params = $this->getRequest()->getParams();
        $product = $this->_initProduct();

        /**
         * Check product availability
         */
        if (!$product) {
            $message = __('We can\'t add this item to your shopping cart right now.');
            return $this->addToCartResponse($message, 0);
        }
        $this->setProduct($product);

        try {
            if ($this->isShowOptionResponse($product, $params)) {
                $configurableParams = isset($params['super_attribute']) ? $params['super_attribute'] : null;
                return $this->showOptionsResponse($product, $configurableParams);
            }

            if (isset($params['qty'])) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    ['locale' => $this->localeResolver->getLocale()]
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $related = $this->getRequest()->getParam('related_product');
            $this->cart->addProduct($product, $params);
            if (!empty($related)) {
                $this->cart->addProductsByIds(explode(',', $related));
            }

            $this->cart->save();

            if ($product->getTypeId() == Configurable::TYPE_CODE
                && (bool)$this->helper->getModuleConfig('confirm_display/configurable_image')
            ) {
                $simpleProduct = $product->getTypeInstance()
                    ->getProductByAttributes($params['super_attribute'], $product);
                $this->_coreRegistry->register('amasty_cart_simple_product', $simpleProduct);
            } else {
                $this->_coreRegistry->unregister('amasty_cart_simple_product');
            }

            $this->_eventManager->dispatch(
                'checkout_cart_add_product_complete',
                ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
            );

            if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                if (!$this->cart->getQuote()->getHasError()) {
                    $message = '<p>' . __(
                            '%1 has been added to your cart',
                            '<a href="' . $product->getProductUrl() .'" title=" . ' .
                            $product->getName() . '">' .
                            $product->getName() .
                            '</a>'
                        ) . '</p>';

                    $message = $this->getProductAddedMessage($product, $message);
                    return $this->addToCartResponse($message, 1);
                } else {
                    $message = [];
                    $errors = $this->cart->getQuote()->getErrors();
                    foreach ($errors as $error) {
                        $message[] = $error->getText();
                    }

                    return $this->showMessages($message);
                }
            }
        } catch (LocalizedException $e) {
            return $this->showMessages([nl2br($this->escaper->escapeHtml($e->getMessage()))]);

        } catch (\Exception $e) {
            $message = __('We can\'t add this item to your shopping cart right now.');
            $message .= $e->getMessage();
            return $this->addToCartResponse($message, 0);
        }
    }

    /**
     * If product is composite - show popup with options
     * @param array $message
     *
     * @return mixed
     */
    protected function showMessages($message)
    {
        $product = $this->getProduct();
        if (!$product->isComposite()) {
            return $this->addToCartResponse(implode(', ', $message), 0);
        } else {
            $this->messages = $message;
            return $this->showOptionsResponse($product, null);
        }
    }

    protected function isShowOptionResponse($product, $params)
    {
        $requiredOptions = $product->getTypeInstance()->hasRequiredOptions($product);
        $showOptionsResponse = false;
        switch ($product->getTypeId()) {
            case 'configurable':
                $attributesCount = $product->getTypeInstance()->getConfigurableAttributes($product)->count();
                $superParamsCount = (array_key_exists('super_attribute', $params)) ?
                    count(array_filter($params['super_attribute'])) : 0;
                if (isset($params['configurable-option'])) {
                    // compatibility with Amasty_Conf product matrix
                    $matrixSelected = false;
                    foreach ($params['amconfigurable-option'] as $amConfigurableOption) {
                        $optionData = $this->helper->decode($amConfigurableOption);
                        if (isset($optionData['qty']) && $optionData['qty'] > 0) {
                            $matrixSelected = true;
                            break;
                        }
                    }
                    if (!$matrixSelected) {
                        $this->messages[] = __('Please specify the quantity of product(s).');
                        $showOptionsResponse = true;
                    }
                } elseif ($attributesCount != $superParamsCount) {
                    $showOptionsResponse = true;
                }
                break;
            case 'grouped':
                if (!array_key_exists('super_group', $params)) {
                    $showOptionsResponse = true;
                }
                break;
            case 'bundle':
                if (!array_key_exists('bundle_option', $params)) {
                    $showOptionsResponse = true;
                }
                break;
            case 'downloadable':
                if ($requiredOptions && !array_key_exists('links', $params) && !array_key_exists('options', $params)) {
                    $showOptionsResponse = true;
                }
                break;
            case 'simple':
            case 'virtual':
                // required custom options
                if ($requiredOptions && !array_key_exists('options', $params)) {
                    $showOptionsResponse = true;
                }
                break;
        }

        /* not required custom options block*/
        if (!$this->helper->isRedirectToProduct()
            && $product->getOptions()
            && $this->helper->getModuleConfig('dialog_popup/display_options') == Option::ALL_OPTIONS
            && !(array_key_exists('options', $params) || $this->getRequest()->getParam('product_page') == "true")
        ) {
            $showOptionsResponse = true;
        }

        $result = $this->objectFactory->create(['data' => ['show_options_response' => $showOptionsResponse]]);
        $this->_eventManager->dispatch(
            'amasty_cart_add_is_show_option_response_after',
            ['controller' => $this, 'result' => $result]
        );

        return $result->getShowOptionsResponse();
    }

    /**
     * @return bool
     */
    private function isMiniPage()
    {
        return $this->helper->getModuleConfig('dialog_popup/confirm_popup') == ConfirmPopup::MINI_PAGE;
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
        if ($this->helper->isRedirectToProduct()
            && $this->getRequest()->getParam('product_page') == "false"
        ) {
            $result['redirect'] = $product->getProductUrl();
            $resultObject = $this->objectFactory->create(['data' => ['result' => $result]]);
            $this->messageManager->addNoticeMessage(__('You need to choose options for your item.'));

            return $this->getResponse()->representJson(
                $this->helper->encode($resultObject->getResult())
            );
        }

        $this->_productHelper->initProduct($product->getEntityId(), $this);
        $page = $this->resultPageFactory->create(false, ['isIsolated' => true]);
        $page->addHandle('catalog_product_view');

        $type = $product->getTypeId();
        $page->addHandle('catalog_product_view_type_' . $type);

        $optionsHtml = $this->generateOptionsHtml($product, $page, $submitRoute);

        $isMiniPage = $this->helper->isRedirectToProduct() ? 1 : $this->isMiniPage();

        if ($isMiniPage) {
            $block = $page->getLayout()->createBlock(
                \Amasty\Cart\Block\Product\Minipage::class,
                'amasty.cart.minipage',
                [ 'data' =>
                      [
                        'product' => $product,
                        'optionsHtml' => $optionsHtml,
                        'imageBuilder' => $this->imageBuilder,
                        'pageFactory' => $this->resultPageFactory
                    ]
                ]
            );
            $message = $block->toHtml();
            $cancelTitle = __('Continue shopping');
        } else {
            $message = $optionsHtml;
            $cancelTitle = __('Cancel');
        }

        $result = [
            'title'     =>  __('Set options'),
            'message'   =>  $message,
            'b2_name'   =>  __('Add to cart'),
            'b1_name'   =>  $cancelTitle,
            'b2_action' =>  'self.submitFormInPopup();',
            'b1_action' =>  'self.confirmHide();',
            'align' =>  'self.confirmHide();' ,
            'is_add_to_cart' =>  '0',
            'is_minipage' => $isMiniPage ? true : false
        ];

        if ($selectedOptions) {
            $result['selected_options'] = $selectedOptions;
        }

        $resultObject = $this->objectFactory->create(['data' => ['result' => $result]]);
        $this->_eventManager->dispatch(
            'amasty_cart_add_show_option_response_after',
            ['controller' => $this, 'product' => $product, 'result' => $resultObject]
        );

        return $this->getResponse()->representJson(
            $this->helper->encode($resultObject->getResult())
        );
    }

    /**
     * Generate html for product options
     * @param Product $product
     * @param $page
     * @param string|null $submitRoute
     *
     * @return mixed|string
     */
    protected function generateOptionsHtml(Product $product, $page, $submitRoute)
    {
        $block = $page->getLayout()->getBlock('product.info');
        if (!$block) {
            $block = $page->getLayout()->createBlock(
                \Magento\Catalog\Block\Product\View::class,
                'product.info',
                [ 'data' => [] ]
            );
        }

        $block->setProduct($product);
        if ($submitRoute) {
            $block->setData('submit_route_data', [
                'route' => $submitRoute
            ]);
        }
        $html = $block->toHtml();

        $html = str_replace(
            '"spConfig',
            '"priceHolderSelector": ".price-box[data-product-id=' . $product->getId() . ']", "spConfig',
            $html
        );

        if ($submitRoute === \Amasty\Cart\Controller\Wishlist\Cart::WISHLIST_URL) {
            $html = str_replace(
                '</form>',
                '<input name="item" type="hidden" value="'
                            . (int)$this->getRequest()->getParam('item') . '"> </form>',
                $html
            );
        }

        $contentClass = 'product-options-bottom';
        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            $contentClass .= ' product-item';
        }

        $errors = '';
        if (count($this->messages)) {
            $errors .= '<div class="message error">' . implode(' ', $this->messages) . '</div>';
        }

        $isMiniPage = $this->helper->isRedirectToProduct() ? 1 : $this->isMiniPage();

        if ($isMiniPage) {
            $title = '';
        } else {
            $title = '<a href="' . $product->getProductUrl() . '" title="' . $product->getName() . '" class="added-item">' .
                $product->getName() .
                '</a>';
        }

        $html = '<div class="' . $contentClass . '" >' .
            $title .
            $errors .
            $html .
            '</div>';
        $html = $this->replaceHtmlElements($html, $product);

        return $html;
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

            $block->setQtyHtml($this->getQtyBlockHtml($product));
            $block->setProduct($product);

            $message = $block->toHtml();
        } else {
            $message .= $this->getQtyBlockHtml($product);
        }

        //display count cart item
        if ($this->helper->isDisplayCount()) {
            $summary = $this->cart->getSummaryQty();
            $cartUrl = $this->cartHelper->getCartUrl();
            if ($summary == 1) {
                $partOne = __('There is');
                $partTwo = __(' item');
            } else {
                $partOne = __('There are');
                $partTwo = __(' items');
            }

            $message .=
                "<p id='amcart-count' class='text'>".
                $partOne .
                ' <a href="'. $cartUrl .'" id="am-a-count" data-amcart="amcart-count" title="' . __('View Cart') . '">'.
                $summary.  $partTwo .
                '</a> '.
                __(' in your cart.') .
                "</p>";
        }

        //display sum price
        if ($this->helper->isDisplaySubtotal()) {
            $message .=
                '<p class="amcart-subtotal text">' .
                __('Cart Subtotal:') .
                ' <span class="am_price" data-amcart="amcart-price">'.
                $this->getSubtotalHtml() .
                '</span></p>';
        }

        $type = $this->helper->getModuleConfig('selling/block_type');
        if ($type && $type !== '0') {
            /* replace uenc for correct redirect*/
            $refererUrl = $this->_request->getServer('HTTP_REFERER');
            $message = $this->replaceUenc($refererUrl, $message);
        }

        return $message;
    }

    /**
     * @param $message
     * @param $status
     * @param array $additionalResult
     *
     * @return mixed
     */
    protected function addToCartResponse($message, $status, $additionalResult = [])
    {
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
                    $category = $this->categoryFactory->create()->load($categoryId);
                    if ($category) {
                        $result['b1_action'] = 'document.location = "' .
                            $category->getUrl()
                            . '";';
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
     * @return string
     */
    protected function getAdditionalBlockHtml()
    {
        //display related products
        $product = $this->getProduct();
        $type = $this->helper->getModuleConfig('selling/block_type');
        $html = '';
        if ($type && $type !== '0' && $product) {
            $this->_productHelper->initProduct($product->getEntityId(), $this);
            $this->layout->createBlock(
                \Magento\Framework\Pricing\Render::class,
                'product.price.render.default',
                ['data' => [
                    'price_render_handle' => 'catalog_product_prices',
                    'use_link_for_as_low_as' => true
                ]]
            );
            $block = $this->layout->createBlock(
                'Amasty\Cart\Block\Product\\' . ucfirst($type),
                'amasty.cart.product_' . $type,
                ['data' => []]
            );
            $block->setProduct($product)->setTemplate("Amasty_Cart::product/list/items.phtml");
            $html = $block->toHtml();
            $refererUrl = $product->getProductUrl();
            $html = $this->replaceUenc($refererUrl, $html);
        }

        return $html;
    }

    /**
     * @return string
     */
    protected function getSubtotalHtml()
    {
        $totals = $this->cart->getQuote()->getTotals();
        $subtotal = isset($totals['subtotal']) && $totals['subtotal'] instanceof Total
            ? $totals['subtotal']->getValue()
            : 0;

        return $this->helperData->formatPrice($subtotal);
    }

    /**
     * @param mixed $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param string $refererUrl
     * @param string $item
     * @return string mixed
     */
    private function replaceUenc($refererUrl, $item)
    {
        $currentUenc = $this->urlHelper->getEncodedUrl();
        $newUenc = $this->urlHelper->getEncodedUrl($refererUrl);
        return str_replace($currentUenc, $newUenc, $item);
    }

    /**
     * @param Product $product
     * @return string
     */
    private function getQtyBlockHtml($product)
    {
        $result = '';
        if ($this->helper->isChangeQty()) {
            $quoteItem = $this->getItemByProduct($product, $this->cart->getQuote());
            if ($quoteItem) {
                $block = $this->layout->getBlock('amasty.cart.qty');
                if (!$block) {
                    $block = $this->layout->createBlock(
                        \Amasty\Cart\Block\Product::class,
                        'amasty.cart.qty',
                        ['data' => []]
                    );
                }

                $block->setTemplate('Amasty_Cart::qty.phtml');
                $block->setQty($quoteItem->getQty());
                $block->setQuoteId($quoteItem->getData('item_id'));

                $result = $block->toHtml();
            }
        }

        return $result;
    }

    /**
     * Compare products by id
     * @param Product $product
     * @param \Magento\Quote\Model\Quote $quote
     * @return bool
     */
    public function getItemByProduct($product, $quote)
    {
        $productId = $product->getId();
        $result = false;
        foreach ($quote->getAllItems() as $item) {
            if ($item->getProduct()->getId() == $productId) {
                $result = $item;
                break;
            }
        }

        return $result;
    }

    private function replaceHtmlElements($html, $product)
    {
        /* replace uenc for correct redirect*/
        $currentUenc = $this->urlHelper->getEncodedUrl();
        $refererUrl = $product->getProductUrl();
        $newUenc = $this->urlHelper->getEncodedUrl($refererUrl);

        $html = str_replace($currentUenc, $newUenc, $html);
        $html = str_replace('"swatch-opt"', '"swatch-opt swatch-opt-' . $product->getId() . '"', $html);
        $html = str_replace('spConfig": {"attributes', 'spConfig": {"containerId":"#confirmBox", "attributes', $html);
        $html = str_replace('[data-role=swatch-options]', '#confirmBox [data-role=swatch-options]', $html);

        return $html;
    }
}
