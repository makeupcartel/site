<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */
namespace Amasty\Cart\Helper;

use Amasty\Cart\Model\Source\DisplayElements;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Amasty\Cart\Plugin\DataPost\Replacer;
use Magento\Customer\Model\SessionFactory;

class Data extends AbstractHelper
{
    const SWATCHES_SLIDER = 'amasty_conf/general/swatches_slider';

    const SLICK_STYLES = 'Amasty_Conf::vendor/slick/amslick.min.css';

    const CAROUSEL_STYLES = 'Amasty_Cart::css/vendor/owl.carousel.min.css';

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    private $jsonDecoder;

    /**
     * @var SessionFactory
     */
    private $sessionFactory;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        SessionFactory $sessionFactory
    ) {
        parent::__construct($context);
        $this->jsonEncoder = $jsonEncoder;
        $this->jsonDecoder = $jsonDecoder;
        $this->sessionFactory = $sessionFactory;
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    public function getModuleConfig($path)
    {
        return $this->scopeConfig->getValue('amasty_cart/' . $path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return int
     */
    public function getTime()
    {
        return (int)$this->getModuleConfig('confirm_popup/time');
    }

    /**
     * @return bool
     */
    public function isUsedOnProductPage()
    {
        return (bool)$this->getModuleConfig('confirm_popup/use_on_product_page');
    }

    /**
     * @return string
     */
    public function getProductButton()
    {
        return $this->getModuleConfig('confirm_popup/product_button');
    }

    /**
     * @return array
     */
    protected function getDisplayElements()
    {
        $elements = $this->getModuleConfig('confirm_display/display_elements');
        $elements = explode(',', $elements);

        return $elements;
    }

    /**
     * @return string
     */
    public function getDisplayAlign()
    {
        return $this->getModuleConfig('confirm_display/align');
    }

    /**
     * @return bool
     */
    public function isDisplayImageBlock()
    {
        return in_array(DisplayElements::IMAGE, $this->getDisplayElements());
    }

    /**
     * @return bool
     */
    public function isDisplayCount()
    {
        return in_array(DisplayElements::COUNT, $this->getDisplayElements());
    }

    /**
     * @return bool
     */
    public function isDisplaySubtotal()
    {
        return in_array(DisplayElements::SUBTOTAL, $this->getDisplayElements());
    }

    /**
     * @return bool
     */
    public function isDisplayGoToCheckout()
    {
        return in_array(DisplayElements::CHECKOUT_BUTTON, $this->getDisplayElements());
    }

    /**
     * @param $block
     *
     * @return array
     */
    public function jsParam($block)
    {
        $param = [
            'send_url'           => $this->_getUrl('amasty_cart/cart/add'),
            'src_image_progress' => $block->getViewFileUrl('Amasty_Cart::images/loading.gif'),
            'type_loading'       => $this->getModuleConfig('general/type_loading'),
            'align'              => $this->getDisplayAlign(),
            'open_minicart'      => $this->isOpenMinicart()
        ];

        return $param;
    }


    /**
     * @return bool
     */
    public function isOpenMinicart()
    {
        return (bool)$this->getModuleConfig('general/open_minicart');
    }

    /**
     * @param $data
     *
     * @return string
     */
    public function encode($data)
    {
        return $this->jsonEncoder->encode($data);
    }

    /**
     * @param string $data
     *
     * @return array
     */
    public function decode($data)
    {
        return $this->jsonDecoder->decode($data);
    }

    public function getLeftButtonColor()
    {
        return $this->getModuleConfig('visual/left_button');
    }

    public function getRightButtonColor()
    {
        return $this->getModuleConfig('visual/right_button');
    }

    public function getBackgroundColor()
    {
        return $this->getModuleConfig('visual/background');
    }

    public function getProductNameColor()
    {
        return $this->getModuleConfig('visual/product_name');
    }

    public function getTextColor()
    {
        return $this->getModuleConfig('visual/text');
    }

    public function getButtonTextColor()
    {
        return $this->getModuleConfig('visual/button_text');
    }

    public function colourBrightness($hex, $percent)
    {
        // Work out if hash given
        $hash = '';
        if (stristr($hex, '#')) {
            $hex = str_replace('#', '', $hex);
            $hash = '#';
        }
        /// HEX TO RGB
        $rgb = [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        ];
        //// CALCULATE
        for ($i=0; $i<3; $i++) {
            // See if brighter or darker
            if ($percent > 0) {
                // Lighter
                $rgb[$i] = round($rgb[$i] * $percent) + round(255 * (1-$percent));
            } else {
                // Darker
                $positivePercent = $percent - ($percent*2);
                $rgb[$i] = round($rgb[$i] * $positivePercent) + round(0 * (1-$positivePercent));
            }
            // In case rounding up causes us to go to 256
            if ($rgb[$i] > 255) {
                $rgb[$i] = 255;
            }
        }
        //// RBG to Hex
        $hex = '';
        for ($i=0; $i < 3; $i++) {
            // Convert the decimal digit to hex
            $hexDigit = dechex($rgb[$i]);
            // Add a leading zero if necessary
            if (strlen($hexDigit) == 1) {
                $hexDigit = "0" . $hexDigit;
            }
            // Append to the hex string
            $hex .= $hexDigit;
        }
        return $hash.$hex;
    }

    public function getUrl($route, $params = [])
    {
        return parent::_getUrl($route, $params);
    }

    /**
     * @return int
     */
    public function isShowProductQty()
    {
        return (int)$this->getModuleConfig('general/show_qty_product');
    }

    public function getInfoMessage()
    {
        return __('in your cart');
    }

    /**
     * @return int
     */
    public function getProductsQtyLimit()
    {
        return (int)$this->getModuleConfig('selling/products_qty_limit');
    }

    /**
     * @return bool
     */
    public function isWishlistAjax()
    {
        return $this->getModuleConfig('general/wishlist')
            && $this->isCustomerLogged();
    }

    /**
     * @return bool
     */
    public function isCompareAjax()
    {
        return (bool)$this->getModuleConfig('general/compare');
    }

    /**
     * Check if need include dataPostAjax.js
     *
     * @return bool
     */
    public function isActionsAjax()
    {
        return $this->isWishlistAjax()
            || $this->isCompareAjax();
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public function getDataPost($type)
    {
        $result = Replacer::DATA_POST;
        if (($type == 'compare' && $this->isCompareAjax())
            || ($type == 'wishlist' && $this->isWishlistAjax())
        ) {
            $result = Replacer::DATA_POST_AJAX;
        }

        return $result;
    }
    
    /**
     * @return bool
     */
    public function isChangeQty()
    {
        return in_array(DisplayElements::QTY, $this->getDisplayElements());
    }

    /**
     * @return bool
     */
    public function isCustomerLogged()
    {
        return $this->sessionFactory->create()->isLoggedIn();
    }

    /**
     * Check if swatches slider from Amasty_Conf enabled
     *
     * @return bool
     */
    public function isSliderWork()
    {
        return $this->_moduleManager->isEnabled('Amasty_Conf')
            && $this->scopeConfig->getValue(self::SWATCHES_SLIDER, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getBlockTitle()
    {
        return $this->getModuleConfig('selling/block_title') ?: __('More Choices:');
    }

    /**
     * @return string
     */
    public function getBlockSubTitle()
    {
        return $this->getModuleConfig('selling/block_subtitle')
            ?: __('You may be interested in the following items');
    }

    /**
     * @return bool
     */
    public function isRedirectToProduct()
    {
        return (bool)$this->getModuleConfig('dialog_popup/redirect_to_product');
    }
}
