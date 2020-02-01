<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Plugin\Bundle\Pricing\Price;

class BundleOptionPrice
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
     * Register Flags for load collection without restricted filter and avoid change isSalable
     * For correct price
     */
    protected function registerIgnores()
    {
        $this->coreRegistry->register('amasty_ignore_product_filter', true, true);
        $this->coreRegistry->register('amasty_dont_change_isSalable', true, true);
    }

    /**
     * Unregister Flags
     * For correct price
     */
    protected function unregisterIgnores()
    {
        $this->coreRegistry->unregister('amasty_ignore_product_filter');
        $this->coreRegistry->unregister('amasty_dont_change_isSalable');
    }

    /**
     * @param \Magento\Bundle\Pricing\Price\BundleOptionPrice         $subject
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetValue(
        \Magento\Bundle\Pricing\Price\BundleOptionPrice $subject
    ) {
        $this->registerIgnores();
    }

    /**
     * @param \Magento\Bundle\Pricing\Price\BundleOptionPrice $subject
     * @param float                                                                $price
     *
     * @return float
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetValue(
        \Magento\Bundle\Pricing\Price\BundleOptionPrice $subject,
        $price
    ) {
        $this->unregisterIgnores();
        return $price;
    }

    /**
     * @param \Magento\Bundle\Pricing\Price\BundleOptionPrice         $subject
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetMaxValue(
        \Magento\Bundle\Pricing\Price\BundleOptionPrice $subject
    ) {
        $this->registerIgnores();
    }

    /**
     * Show correct price for Bundle product
     *
     * @param \Magento\Bundle\Pricing\Price\BundleOptionPrice $subject
     * @param float                                                                $price
     *
     * @return float
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetMaxValue(
        \Magento\Bundle\Pricing\Price\BundleOptionPrice $subject,
        $price
    ) {
        $this->unregisterIgnores();
        return $price;
    }

    /**
     * @param \Magento\Bundle\Pricing\Price\BundleOptionPrice         $subject
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetAmount(
        \Magento\Bundle\Pricing\Price\BundleOptionPrice $subject
    ) {
        $this->registerIgnores();
    }

    /**
     * @param \Magento\Bundle\Pricing\Price\BundleOptionPrice $subject
     * @param float                                                                $price
     *
     * @return float
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAmount(
        \Magento\Bundle\Pricing\Price\BundleOptionPrice $subject,
        $price
    ) {
        $this->unregisterIgnores();
        return $price;
    }
}
