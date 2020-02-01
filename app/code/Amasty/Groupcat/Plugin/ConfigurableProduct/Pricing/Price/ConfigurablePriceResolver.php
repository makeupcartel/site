<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Plugin\ConfigurableProduct\Pricing\Price;

class ConfigurablePriceResolver
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    public function __construct(\Magento\Framework\Registry $coreRegistry)
    {
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Avoid error "no simple in configurable" on price resolving
     *
     * @param \Magento\ConfigurableProduct\Pricing\Price\ConfigurablePriceResolver         $subject
     * @param  \Magento\Framework\Pricing\SaleableInterface|\Magento\Catalog\Model\Product $product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeResolvePrice(
        \Magento\ConfigurableProduct\Pricing\Price\ConfigurablePriceResolver $subject,
        $product
    ) {
        $this->coreRegistry->register('amasty_ignore_product_filter', true, true);
    }

    /**
     * @param \Magento\ConfigurableProduct\Pricing\Price\ConfigurablePriceResolver $subject
     * @param float                                                                $price
     *
     * @return float
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterResolvePrice(
        \Magento\ConfigurableProduct\Pricing\Price\ConfigurablePriceResolver $subject,
        $price
    ) {
        $this->coreRegistry->unregister('amasty_ignore_product_filter');
        return $price;
    }
}
