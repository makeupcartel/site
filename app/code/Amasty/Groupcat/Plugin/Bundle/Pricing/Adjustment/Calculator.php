<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Plugin\Bundle\Pricing\Adjustment;

class Calculator
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
     * @param \Magento\Bundle\Pricing\Adjustment\Calculator $subject
     * @param                                               $amount
     * @param                                               $saleableItem
     * @param null                                          $exclude
     * @param array                                         $context
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetAmount(
        \Magento\Bundle\Pricing\Adjustment\Calculator $subject,
        $amount,
        $saleableItem,
        $exclude = null,
        $context = []
    ) {
        $this->registerIgnores();
    }

    /**
     * @param \Magento\Bundle\Pricing\Adjustment\Calculator $subject
     * @param float                                         $price
     *
     * @return float
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAmount(
        \Magento\Bundle\Pricing\Adjustment\Calculator $subject,
        $price
    ) {
        $this->unregisterIgnores();

        return $price;
    }

    /**
     * @param \Magento\Bundle\Pricing\Adjustment\Calculator $subject
     * @param                                               $amount
     * @param                                               $saleableItem
     * @param null                                          $exclude
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetMinRegularAmount(
        \Magento\Bundle\Pricing\Adjustment\Calculator $subject,
        $amount,
        $saleableItem,
        $exclude = null
    ) {
        $this->registerIgnores();
    }

    /**
     * @param \Magento\Bundle\Pricing\Adjustment\Calculator $subject
     * @param float                                         $price
     *
     * @return float
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetMinRegularAmount(
        \Magento\Bundle\Pricing\Adjustment\Calculator $subject,
        $price
    ) {
        $this->unregisterIgnores();

        return $price;
    }

    /**
     * @param \Magento\Bundle\Pricing\Adjustment\Calculator $subject
     * @param                                               $amount
     * @param                                               $saleableItem
     * @param null                                          $exclude
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetMaxAmount(
        \Magento\Bundle\Pricing\Adjustment\Calculator $subject,
        $amount,
        $saleableItem,
        $exclude = null
    ) {
        $this->registerIgnores();
    }

    /**
     * @param \Magento\Bundle\Pricing\Adjustment\Calculator $subject
     * @param float                                         $price
     *
     * @return float
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetMaxAmount(
        \Magento\Bundle\Pricing\Adjustment\Calculator $subject,
        $price
    ) {
        $this->unregisterIgnores();

        return $price;
    }

    /**
     * @param \Magento\Bundle\Pricing\Adjustment\Calculator $subject
     * @param                                               $amount
     * @param                                               $saleableItem
     * @param null                                          $exclude
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetMaxRegularAmount(
        \Magento\Bundle\Pricing\Adjustment\Calculator $subject,
        $amount,
        $saleableItem,
        $exclude = null
    ) {
        $this->registerIgnores();
    }

    /**
     * @param \Magento\Bundle\Pricing\Adjustment\Calculator $subject
     * @param float                                         $price
     *
     * @return float
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetMaxRegularAmount(
        \Magento\Bundle\Pricing\Adjustment\Calculator $subject,
        $price
    ) {
        $this->unregisterIgnores();

        return $price;
    }

    /**
     * @param \Magento\Bundle\Pricing\Adjustment\Calculator $subject
     * @param                                               $saleableItem
     * @param null                                          $exclude
     * @param bool                                          $searchMin
     * @param float                                         $baseAmount
     * @param bool                                          $useRegularPrice
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetOptionsAmount(
        \Magento\Bundle\Pricing\Adjustment\Calculator $subject,
        $saleableItem,
        $exclude = null,
        $searchMin = true,
        $baseAmount = 0.,
        $useRegularPrice = false
    ) {
        $this->registerIgnores();
    }

    /**
     * @param \Magento\Bundle\Pricing\Adjustment\Calculator $subject
     * @param float                                         $price
     *
     * @return float
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetOptionsAmount(
        \Magento\Bundle\Pricing\Adjustment\Calculator $subject,
        $price
    ) {
        $this->unregisterIgnores();

        return $price;
    }

    /**
     * @param \Magento\Bundle\Pricing\Adjustment\Calculator $subject
     * @param                                               $amount
     * @param                                               $saleableItem
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetAmountWithoutOption(
        \Magento\Bundle\Pricing\Adjustment\Calculator $subject,
        $amount,
        $saleableItem
    ) {
        $this->registerIgnores();
    }

    /**
     * @param \Magento\Bundle\Pricing\Adjustment\Calculator $subject
     * @param float                                         $price
     *
     * @return float
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAmountWithoutOption(
        \Magento\Bundle\Pricing\Adjustment\Calculator $subject,
        $price
    ) {
        $this->unregisterIgnores();

        return $price;
    }
}
