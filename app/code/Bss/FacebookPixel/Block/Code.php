<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_FacebookPixel
 * @author    Extension Team
 * @copyright Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\FacebookPixel\Block;

/**
 * Class Code
 * @package Bss\FacebookPixel\Block
 */
class Code extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Bss\FacebookPixel\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $catalogHelper;

    /**
     * Tax display flag
     *
     * @var null|int
     */
    protected $taxDisplayFlag = null;

    /**
     * Tax catalog flag
     *
     * @var null|int
     */
    protected $taxCatalogFlag = null;

    /**
     * Store object
     *
     * @var null|\Magento\Store\Model\Store
     */
    protected $store = null;

    /**
     * Store ID
     *
     * @var null|int
     */
    protected $storeId = null;

    /**
     * Base currency code
     *
     * @var null|string
     */
    protected $baseCurrencyCode = null;

    /**
     * Current currency code
     *
     * @var null|string
     */
    protected $currentCurrencyCode = null;

    /**
     * @var \Magento\Checkout\Model\SessionFactory
     */
    protected $checkoutSession;

    /**
     * @var \Bss\FacebookPixel\Model\SessionFactory
     */
    protected $fbPixelSession;

    /**
     * Code constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Bss\FacebookPixel\Helper\Data $helper
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Catalog\Helper\Data $catalogHelper
     * @param \Magento\Checkout\Model\SessionFactory $checkoutSession
     * @param \Bss\FacebookPixel\Model\SessionFactory $fbPixelSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Bss\FacebookPixel\Helper\Data $helper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Helper\Data $catalogHelper,
        \Magento\Checkout\Model\SessionFactory $checkoutSession,
        \Bss\FacebookPixel\Model\SessionFactory $fbPixelSession,
        array $data = []
    ) {
        $this->storeManager  = $context->getStoreManager();
        $this->helper        = $helper;
        $this->coreRegistry  = $coreRegistry;
        $this->catalogHelper = $catalogHelper;
        $this->checkoutSession = $checkoutSession;
        $this->fbPixelSession = $fbPixelSession;
        parent::__construct($context, $data);
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function checkDisable()
    {
        $data   = $this->getFacebookPixelData();
        $action = $data['full_action_name'];
        $listDisableCode = $this->helper->listPageDisable();
        if (($action == 'checkout_onepage_success'
                || $action == 'onepagecheckout_index_success') && in_array('success_page', $listDisableCode)) {
            return 404;
        } elseif ($action == 'customer_account_index' && in_array('account_page', $listDisableCode)) {
            return 404;
        } elseif (($action == 'cms_index_index' || $action == 'cms_page_view')
            && in_array('cms_page', $listDisableCode)) {
            return 404;
        } else {
            return $this->checkDisableMore($action, $listDisableCode);
        }
    }

    /**
     * @param  string $action
     * @param array $listDisableCode
     * @return int
     */
    private function checkDisableMore($action, $listDisableCode)
    {
        $arrCheckout = [
            'checkout_index_index',
            'onepagecheckout_index_index',
            'onestepcheckout_index_index',
            'opc_index_index'
        ];
        if (in_array($action, $arrCheckout) && in_array('checkout_page', $listDisableCode)) {
            return 404;
        }
        if ($action == 'catalogsearch_result_index' && in_array('search_page', $listDisableCode)) {
            return 404;
        }
        if ($action == 'catalog_product_view' && in_array('product_page', $listDisableCode)) {
            return 404;
        }
        if ($action == 'customer_account_create' && in_array('registration_page', $listDisableCode)) {
            return 404;
        }
        return $this->checkDisableMore2($action, $listDisableCode);
    }

    /**
     * @param string $action
     * @param array $listDisableCode
     * @return int
     */
    private function checkDisableMore2($action, $listDisableCode)
    {
        if (($action == 'catalogsearch_advanced_result'
            || $action == 'catalogsearch_advanced_index') && in_array('advanced_search_page', $listDisableCode)) {
            return 404;
        }
        if ($action == 'catalog_category_view' && in_array('category_page', $listDisableCode)) {
            return 404;
        }
        return 'pass';
    }
    /**
     * @return false|int|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProduct()
    {
        $productData = 404;
        $data   = $this->getFacebookPixelData();
        $action = $data['full_action_name'];
        if ($action == 'catalog_product_view') {
            if ($this->getProductData() !== null) {
                $productData = $this->helper->serializes($this->getProductData());
            }
        }
        return $productData;
    }

    /**
     * @return false|int|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCategory()
    {
        $categoryData = 404;
        $data   = $this->getFacebookPixelData();
        $action = $data['full_action_name'];
        if ($action == 'catalog_category_view') {
            if ($this->getCategoryData() !== null) {
                $categoryData = $this->helper->serializes($this->getCategoryData());
            }
        }
        return $categoryData;
    }

    /**
     * @return array|int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOrder()
    {
        $orderData = 404;
        $data   = $this->getFacebookPixelData();
        $action = $data['full_action_name'];
        if ($action == 'checkout_onepage_success'
            || $action == 'onepagecheckout_index_success'
            || $action == 'multishipping_checkout_success') {
            $orderData = $this->getOrderData();
        }
        return $orderData;
    }

    /**
     * @return int|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRegistration()
    {
        $session = $this->fbPixelSession->create();
        $registration = 404;
        if ($this->helper->getConfig('bss_facebook_pixel/event_tracking/registration')
            && $session->hasRegister()) {
            $registration = $this->helper->getPixelHtml($session->getRegister());
        }
        return $registration;
    }

    /**
     * @return int|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAddToWishList()
    {
        $session = $this->fbPixelSession->create();
        $add_to_wishlist = 404;
        if ($this->helper->getConfig('bss_facebook_pixel/event_tracking/add_to_wishlist')
            && $session->hasAddToWishlist()) {
            $add_to_wishlist = $this->helper->getPixelHtml($session->getAddToWishlist());
        }
        return $add_to_wishlist;
    }

    /**
     * @return int|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getInitiateCheckout()
    {
        $session = $this->fbPixelSession->create();
        $initiateCheckout = 404;
        if ($this->helper->getConfig('bss_facebook_pixel/event_tracking/initiate_checkout')
            && $session->hasInitiateCheckout()) {
            $initiateCheckout = $this->helper->getPixelHtml($session->getInitiateCheckout());
        }
        return $initiateCheckout;
    }

    /**
     * @return int|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSearch()
    {
        $session = $this->fbPixelSession->create();
        $search = 404;
        if ($this->helper->getConfig('bss_facebook_pixel/event_tracking/search')
            && $session->hasSearch()) {
            $search = $this->helper->getPixelHtml($session->getSearch());
        }
        return $search;
    }

    /**
     * Returns data needed for purchase tracking.
     *
     * @return array|int
     */
    public function getOrderData()
    {
        $order = $this->checkoutSession->create()->getLastRealOrder();
        $orderId = $order->getIncrementId();

        if ($orderId) {
            $customerEmail = $order->getCustomerEmail();
            if ($order->getShippingAddress()) {
                $addressData = $order->getShippingAddress();
            } else {
                $addressData = $order->getBillingAddress();
            }

            if ($addressData) {
                $customerData = $addressData->getData();
            } else {
                $customerData = null;
            }
            $product = [
                'content_ids' => [],
                'contents' => [],
                'value' => "",
                'currency' => "",
                'num_items' => 0,
                'email' => "",
                'address' => []
            ];

            $num_item = 0;
            foreach ($order->getAllVisibleItems() as $item) {
                $product['contents'][] = [
                    'id' => $item->getSku(),
                    'name' => $item->getName(),
                    'quantity' => (int)$item->getQtyOrdered(),
                    'item_price' => $item->getPrice()
                ];
                $product['content_ids'][] = $item->getSku();
                $num_item += round($item->getQtyOrdered());
            }
            $data = [
                'content_ids' => $product['content_ids'],
                'contents' => $product['contents'],
                'content_type' => 'product',
                'value' => number_format(
                    $order->getGrandTotal(),
                    2,
                    '.',
                    ''
                ),
                'num_items' => $num_item,
                'currency' => $order->getOrderCurrencyCode(),
                'email' => $customerEmail,
                'phone' => $this->getValueByKey($customerData, 'telephone'),
                'firtname' => $this->getValueByKey($customerData, 'firstname'),
                'lastname' => $this->getValueByKey($customerData, 'lastname'),
                'city' => $this->getValueByKey($customerData, 'city'),
                'country' => $this->getValueByKey($customerData, 'country_id'),
                'st' => $this->getValueByKey($customerData, 'region'),
                'zipcode' => $this->getValueByKey($customerData, 'postcode')
            ];
            return $this->helper->serializes($data);
        } else {
            return 404;
        }
    }

    /**
     * @param $array
     * @param $key
     * @return string
     */
    protected function getValueByKey($array, $key)
    {
        if (!empty($array) && isset($array[$key])) {
            return $array[$key];
        }
        return '';
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.RequestAwareBlockMethod)
     */
    public function getFacebookPixelData()
    {
        $data = [];

        $data['id'] = $this->helper->getConfig(
            'bss_facebook_pixel/general/pixel_id'
        );

        $data['full_action_name'] = $this->getRequest()->getFullActionName();

        return $data;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getProductData()
    {
        if (!$this->helper->getConfig(
            'bss_facebook_pixel/event_tracking/product_view'
        )) {
            return [];
        }
        $currentProduct = $this->coreRegistry->registry('current_product');

        $data = [];

        $data['content_name']     = $this->helper
            ->escapeSingleQuotes($currentProduct->getName());
        $data['content_ids']      = $this->helper
            ->escapeSingleQuotes($currentProduct->getSku());
        $data['content_type']     = 'product';
        $data['value']            = $this->formatPrice(
            $this->getProductPrice($currentProduct)
        );
        $data['currency']         = $this->getCurrentCurrencyCode();

        return $data;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCategoryData()
    {
        if (!$this->helper->getConfig(
            'bss_facebook_pixel/event_tracking/category_view'
        )) {
            return [];
        }
        $currentCategory = $this->coreRegistry->registry('current_category');

        $data = [];

        $data['content_name']     = $this->helper
            ->escapeSingleQuotes($currentCategory->getName());
        $data['content_ids']      = $this->helper
            ->escapeSingleQuotes($currentCategory->getId());
        $data['content_type']     = 'category';
        $data['currency']         = $this->getCurrentCurrencyCode();

        return $data;
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface|\Magento\Store\Model\Store|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStore()
    {
        if ($this->store === null) {
            $this->store = $this->storeManager->getStore();
        }

        return $this->store;
    }

    /**
     * @return int|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        if ($this->storeId === null) {
            $this->storeId = $this->getStore()->getId();
        }

        return $this->storeId;
    }

    /**
     * Return base currency code
     * @return string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseCurrencyCode()
    {
        if ($this->baseCurrencyCode === null) {
            $this->baseCurrencyCode = strtoupper(
                $this->getStore()->getBaseCurrencyCode()
            );
        }

        return $this->baseCurrencyCode;
    }

    /**
     * Return current currency code
     * @return string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentCurrencyCode()
    {
        if ($this->currentCurrencyCode === null) {
            $this->currentCurrencyCode = strtoupper(
                $this->getStore()->getCurrentCurrencyCode()
            );
        }

        return $this->currentCurrencyCode;
    }

    /**
     * Returns flag based on "Stores > Configuration > Sales > Tax
     * > Price Display Settings > Display Product Prices In Catalog"
     * Returns 0 or 1 instead of 1, 2, 3.
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getDisplayTaxFlag()
    {
        if ($this->taxDisplayFlag === null) {
            // Tax Display
            // 1 - excluding tax
            // 2 - including tax
            // 3 - including and excluding tax
            $flag = $this->helper->isTaxConfig()->getPriceDisplayType($this->getStoreId());

            // 0 means price excluding tax, 1 means price including tax
            if ($flag == 1) {
                $this->taxDisplayFlag = 0;
            } else {
                $this->taxDisplayFlag = 1;
            }
        }

        return $this->taxDisplayFlag;
    }

    /**
     * Returns Stores > Configuration > Sales > Tax > Calculation Settings
     * > Catalog Prices configuration value
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCatalogTaxFlag()
    {
        // Are catalog product prices with tax included or excluded?
        if ($this->taxCatalogFlag === null) {
            $this->taxCatalogFlag = (int) $this->helper->getConfig(
                'tax/calculation/price_includes_tax'
            );
        }

        // 0 means excluded, 1 means included
        return $this->taxCatalogFlag;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return mixed|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getProductPrice($product)
    {
        switch ($product->getTypeId()) {
            case 'bundle':
                $price =  $this->getBundleProductPrice($product);
                break;
            case 'configurable':
                $price = $this->getConfigurableProductPrice($product);
                break;
            case 'grouped':
                $price = $this->getGroupedProductPrice($product);
                break;
            default:
                $price = $this->getFinalPrice($product);
        }

        return $price;
    }

    /**
     * Returns bundle product price.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getBundleProductPrice($product)
    {
        $includeTax = (bool) $this->getDisplayTaxFlag();

        return $this->getFinalPrice(
            $product,
            $product->getPriceModel()->getTotalPrices(
                $product,
                'min',
                $includeTax,
                1
            )
        );
    }

    /**
     * @param  \Magento\Catalog\Model\Product $product
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getConfigurableProductPrice($product)
    {
        if ($product->getFinalPrice() === 0) {
            $simpleCollection = $product->getTypeInstance()
                ->getUsedProducts($product);

            foreach ($simpleCollection as $simpleProduct) {
                if ($simpleProduct->getPrice() > 0) {
                    return $this->getFinalPrice($simpleProduct);
                }
            }
        }

        return $this->getFinalPrice($product);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getGroupedProductPrice($product)
    {
        $assocProducts = $product->getTypeInstance(true)
            ->getAssociatedProductCollection($product)
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('tax_class_id')
            ->addAttributeToSelect('tax_percent');

        $minPrice = INF;
        foreach ($assocProducts as $assocProduct) {
            $minPrice = min($minPrice, $this->getFinalPrice($assocProduct));
        }

        return $minPrice;
    }

    /**
     * Returns final price.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $price
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getFinalPrice($product, $price = null)
    {
        $price = $this->resultPriceFinal($product, $price);

        $productType = $product->getTypeId();
        //  Apply tax if needed
        // Simple, Virtual, Downloadable products price is without tax
        // Grouped products have associated products without tax
        // Bundle products price already have tax included/excluded
        // Configurable products price already have tax included/excluded
        if ($productType != 'configurable' && $productType != 'bundle') {
            // If display tax flag is on and catalog tax flag is off
            if ($this->getDisplayTaxFlag() && !$this->getCatalogTaxFlag()) {
                $price = $this->catalogHelper->getTaxPrice(
                    $product,
                    $price,
                    true,
                    null,
                    null,
                    null,
                    $this->getStoreId(),
                    false,
                    false
                );
            }
        }

        // Case when catalog prices are with tax but display tax is set to
        // to exclude tax. Applies for all products except for bundle
        if ($productType != 'bundle') {
            // If display tax flag is off and catalog tax flag is on
            if (!$this->getDisplayTaxFlag() && $this->getCatalogTaxFlag()) {
                $price = $this->catalogHelper->getTaxPrice(
                    $product,
                    $price,
                    false,
                    null,
                    null,
                    null,
                    $this->getStoreId(),
                    true,
                    false
                );
            }
        }

        return $price;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param float|int $price
     * @return float
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function resultPriceFinal($product, $price)
    {
        if ($price === null) {
            $price = $product->getFinalPrice();
        }

        if ($price === null) {
            $price = $product->getData('special_price');
        }
        $productType = $product->getTypeId();
        // 1. Convert to current currency if needed

        // Convert price if base and current currency are not the same
        // Except for configurable products they already have currency converted
        if (($this->getBaseCurrencyCode() !== $this->getCurrentCurrencyCode())
            && $productType != 'configurable'
        ) {
            // Convert to from base currency to current currency
            $price = $this->getStore()->getBaseCurrency()
                ->convert($price, $this->getCurrentCurrencyCode());
        }
        return $price;
    }

    /**
     * Returns formated price.
     *
     * @param string $price
     * @param string $currencyCode
     * @return string
     */
    private function formatPrice($price, $currencyCode = '')
    {
        $formatedPrice = number_format($price, 2, '.', '');

        if ($currencyCode) {
            return $formatedPrice . ' ' . $currencyCode;
        } else {
            return $formatedPrice;
        }
    }
}
