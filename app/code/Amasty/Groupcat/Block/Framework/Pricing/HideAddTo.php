<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

namespace Amasty\Groupcat\Block\Framework\Pricing;

use Magento\Catalog\Api\Data\ProductInterface;

class HideAddTo extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Amasty\Groupcat\Helper\Data
     */
    private $helper;

    /**
     * @var \Amasty\Groupcat\Model\ProductRuleProvider
     */
    private $ruleProvider;

    /**
     * HideAddTo constructor.
     *
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Amasty\Groupcat\Helper\Data $helper
     * @param \Amasty\Groupcat\Model\ProductRuleProvider $ruleProvider
     * @param \Magento\Backend\Block\Template\Context $context
     */
    public function __construct(
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Amasty\Groupcat\Helper\Data $helper,
        \Amasty\Groupcat\Model\ProductRuleProvider $ruleProvider,
        \Magento\Backend\Block\Template\Context $context
    ) {
        parent::__construct($context);
        $this->jsonEncoder = $jsonEncoder;
        $this->helper = $helper;
        $this->ruleProvider = $ruleProvider;
    }

    /**
     * Js for for hiding add to compare and add to wishlist buttons on category page
     *
     * @param ProductInterface $product
     * @return string
     */
    public function getHideButtonsHtml(ProductInterface $product)
    {
        $this->prepareData($product);
        return $this->toHtml();
    }

    /**
     * Get relevant path to template
     *
     * @return string
     */
    public function getTemplate()
    {
        return 'Amasty_Groupcat::hideAddTo.phtml';
    }

    /**
     * Prepare data for template
     *
     * @param ProductInterface $product
     */
    public function prepareData(ProductInterface $product)
    {
        $productIdName = 'amhideprice-product-id-' . $product->getId();
        $jsonData = $this->jsonEncoder->encode([
            'parent' => $this->helper->getModuleStoreConfig('developer/parent'),
            'hide_compare' => $this->ruleProvider->getProductIsHideCompare($product),
            'hide_wishlist' => $this->ruleProvider->getProductIsHideWishlist($product)
        ]);

        $this->assign('productIdName', $productIdName);
        $this->assign('jsonData', $jsonData);
    }
}
