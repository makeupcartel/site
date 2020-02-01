<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Plugin\Framework\Pricing;

use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Pricing\Render as PricingRender;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Amasty\Groupcat\Model\Rule;
use Amasty\Groupcat\Model\Rule\PriceActionOptionsProvider;

class Render
{
    /**
     * @var \Amasty\Groupcat\Model\ProductRuleProvider
     */
    private $ruleProvider;

    /**
     * @var \Amasty\Groupcat\Model\RuleRepository
     */
    private $ruleRepository;

    /**
     * @var \Magento\Cms\Model\BlockRepository
     */
    private $blockRepository;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Amasty\Groupcat\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    private $filterProvider;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Amasty\Groupcat\Model\Rule\Pricing\Render
     */
    private $renderModel;

    /**
     * @var \Amasty\Groupcat\Block\Framework\Pricing\Render
     */
    private $requestBlock;

    /**
     * @var \Amasty\Groupcat\Block\Framework\Pricing\HideAddTo
     */
    private $hideAddToBlock;

    /**
     * Render constructor.
     * @param \Amasty\Groupcat\Helper\Data                          $helper
     * @param \Amasty\Groupcat\Model\ProductRuleProvider            $ruleProvider
     * @param \Amasty\Groupcat\Model\RuleRepository                 $ruleRepository
     * @param \Magento\Cms\Model\BlockRepository                    $blockRepository
     * @param \Magento\Customer\Model\Session                       $customerSession
     * @param \Magento\Cms\Model\Template\FilterProvider            $filterProvider
     * @param \Magento\Store\Model\StoreManagerInterface            $storeManager
     * @param \Magento\Framework\Registry                           $coreRegistry
     * @param \Magento\Framework\Event\ManagerInterface             $eventManager
     * @param \Magento\Framework\Json\EncoderInterface              $jsonEncoder
     * @param Rule\Pricing\Render                                   $renderModel
     * @param \Amasty\Groupcat\Block\Framework\Pricing\RequestPopup $requestBlock
     * @param \Amasty\Groupcat\Block\Framework\Pricing\HideAddTo    $hideAddToBlock
     */
    public function __construct(
        \Amasty\Groupcat\Helper\Data $helper,
        \Amasty\Groupcat\Model\ProductRuleProvider $ruleProvider,
        \Amasty\Groupcat\Model\RuleRepository $ruleRepository,
        \Magento\Cms\Model\BlockRepository $blockRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Amasty\Groupcat\Model\Rule\Pricing\Render $renderModel,
        \Amasty\Groupcat\Block\Framework\Pricing\RequestPopup $requestBlock,
        \Amasty\Groupcat\Block\Framework\Pricing\HideAddTo $hideAddToBlock
    ) {
        $this->helper          = $helper;
        $this->ruleProvider    = $ruleProvider;
        $this->ruleRepository  = $ruleRepository;
        $this->blockRepository = $blockRepository;
        $this->customerSession = $customerSession;
        $this->filterProvider  = $filterProvider;
        $this->storeManager    = $storeManager;
        $this->coreRegistry    = $coreRegistry;
        $this->eventManager    = $eventManager;
        $this->jsonEncoder     = $jsonEncoder;
        $this->renderModel     = $renderModel;
        $this->requestBlock    = $requestBlock;
        $this->hideAddToBlock = $hideAddToBlock;
    }

    /**
     * @since 1.2.7 while render default price (show price) - don't change isSaleable
     *
     * @param PricingRender     $subject
     * @param callable          $proceed
     * @param string            $priceCode
     * @param SaleableInterface $saleableItem
     * @param array             $arguments
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundRender(
        PricingRender $subject,
        callable $proceed,
        $priceCode,
        SaleableInterface $saleableItem,
        array $arguments = []
    ) {
        $html = $this->getHideToButtonsJs($priceCode, $saleableItem, $arguments);
        if ($this->renderModel->isNeedRenderPrice($saleableItem, $arguments)) {

            $deleteRegister = false;
            if (!$this->coreRegistry->registry('amasty_dont_change_isSalable')) {
                /** @see \Amasty\Groupcat\Plugin\Catalog\Model\Product\IsAvailable::afterIsSalable */
                $deleteRegister = true;
                $this->coreRegistry->register('amasty_dont_change_isSalable', true, true);
            }

            // Show Price Box
            $html .= $proceed($priceCode, $saleableItem, $arguments);

            if ($deleteRegister) {
                $this->coreRegistry->unregister('amasty_dont_change_isSalable');
            }

            return $html;
        }

        $html .= $this->getNewPriceHtmlBox($priceCode, $saleableItem, $arguments);
        return $html;
    }

    /**
     * Price block can be replaced by CMS or by Popup or hided (return empty price)
     *
     * @param string           $priceCode
     * @param ProductInterface $product
     * @param array            $arguments
     *
     * @return string
     */
    private function getNewPriceHtmlBox($priceCode, $product, $arguments)
    {
        $html = '';
        if ($priceCode != \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE && !isset($arguments['zone'])) {
            return $html;
        }

        switch ($this->ruleProvider->getProductPriceAction($product)) {
            case PriceActionOptionsProvider::REPLACE:
                // replace price with CMS block
                $html = $this->renderModel->getPriceCmsBlockForZone($product, $arguments['zone']);
                break;
            case PriceActionOptionsProvider::REPLACE_REQUEST:
                // replace price with Request popup
                $html = $this->requestBlock->getProductRequestPrice($product);
                break;
        }

        return $html;
    }

    /**
     * Return js code to hide addToCompare addToWishlist buttons
     *
     * @param string $priceCode
     * @param ProductInterface $product
     * @param array $arguments
     * @return string
     */
    private function getHideToButtonsJs($priceCode, $product, $arguments)
    {
        $html = '';
        if ($priceCode != \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE) {
            return $html;
        }

        /* hack for hiding Add to Compare and Add to Wishlist buttons on category page - with javascript*/
        if (isset($arguments['zone']) && ($arguments['zone'] == PricingRender::ZONE_ITEM_LIST
                || $arguments['zone'] == PricingRender::ZONE_ITEM_VIEW)
            && ($this->ruleProvider->getProductIsHideCompare($product)
                || $this->ruleProvider->getProductIsHideWishlist($product))
        ) {
            $html = $this->hideAddToBlock->getHideButtonsHtml($product);
        }
        return $html;
    }

    /**
     * Get Key for caching block content
     *
     * @since 1.2.0 cache contains active rule ids instead customer group
     * @since 1.1.5 added customer group id to the key.
     *              For correct work of hide/show switcher on product list of different group rules
     *
     * @param PricingRender $subject
     * @param string        $value
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCacheKey(PricingRender $subject, $value)
    {
        if ($this->helper->isModuleEnabled() && strpos($value, Rule::CACHE_TAG) === false) {
            $ruleCollection = $this->ruleProvider->getActiveRulesCollection();
            $activeRulesIds = $ruleCollection->getAllIds();
            $key = Rule::CACHE_TAG;
            if (count($activeRulesIds)) {
                $key .= implode('_', $activeRulesIds);
            }

            return $value . $key;
        }
        return $value;
    }
}
