<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Plugin\Catalog\Model\Product;

use Magento\Catalog\Model\Product;

class IsAvailable
{
    protected $canChangeIsAvailable = true;

    /**
     * @var \Amasty\Groupcat\Model\ProductRuleProvider
     */
    private $ruleProvider;

    /**
     * @var \Amasty\Groupcat\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    public function __construct(
        \Amasty\Groupcat\Model\ProductRuleProvider $ruleProvider,
        \Amasty\Groupcat\Helper\Data $helper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->ruleProvider = $ruleProvider;
        $this->helper       = $helper;
        $this->coreRegistry = $coreRegistry;
        $this->eventManager = $eventManager;
    }

    /**
     * GetIsSalable should return true is need to display In Stock
     *
     * @param Product $product
     * @param bool    $isSalable
     *
     * @return bool
     */
    public function afterGetIsSalable(Product $product, $isSalable)
    {
        if ($isSalable || !$this->helper->isModuleEnabled()) {
            return $isSalable;
        }

        if ($product->getData('amasty_change_isSalable')) {
            return true;
        }

        return $isSalable;
    }

    /**
     * Need to return true for display In Stock
     *
     * @param Product $product
     * @param         $isAvailable
     *
     * @return bool
     */
    public function afterIsAvailable(Product $product, $isAvailable)
    {
        // If childrens of Bundle product was restricted by Rule, then should return original value
        if (($product->getTypeId() == \Magento\Bundle\Model\Product\Type::TYPE_CODE
                && $product->hasData('is_salable')
                && $product->getData('is_salable')
            )
            || ($this->canChangeIsAvailable
                && $this->helper->isModuleEnabled()
                && ($product->getData('amasty_change_isSalable')
                    || $this->ruleProvider->getProductPriceAction($product)
                    || $this->ruleProvider->getProductIsHideCart($product)
                ))
        ) {
            return true;
        }

        return $isAvailable;
    }

    /**
     * IsSalable will check IsAvailable.
     *
     * @param Product $product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeIsSalable(Product $product)
    {
        $this->canChangeIsAvailable = false;
    }

    /**
     * Set Product as not salable for restrict Add To Cart action
     *
     * @param Product $product
     * @param bool    $isSalable
     *
     * @return bool
     */
    public function afterIsSalable(Product $product, $isSalable)
    {
        if ($this->isHideCart($product)) {
            $this->canChangeIsAvailable = true;
            $product->setData('amasty_change_isSalable', true);

            return false;
        }

        return $isSalable;
    }

    /**
     * Is set product as is_salable = false for resctrict add to cart
     *
     * @param Product $product
     *
     * @return bool
     */
    private function isHideCart(Product $product)
    {
        $isNeedHide = $this->helper->isModuleEnabled()
        && !$this->coreRegistry->registry('amasty_dont_change_isSalable')
        && (
            $this->ruleProvider->getProductPriceAction($product)
            || $this->ruleProvider->getProductIsHideCart($product)
        );

        $this->eventManager->dispatch(
            'amasty_groupcat_is_hide_addtocart',
            ['item' => $product, 'is_hide_addtocart' => &$isNeedHide]
        );

        return $isNeedHide;
    }
}
