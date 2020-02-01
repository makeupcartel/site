<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

namespace Amasty\Groupcat\Model\Rule\Pricing;

use Magento\Framework\Pricing\Render as PricingRender;
use Magento\Catalog\Api\Data\ProductInterface;
use Amasty\Groupcat\Model\Rule\PriceActionOptionsProvider;

class Render
{
    /**
     * @var \Amasty\Groupcat\Model\ProductRuleProvider
     */
    private $ruleProvider;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Amasty\Groupcat\Helper\Data
     */
    private $helper;

    /**
     * @var \Amasty\Groupcat\Model\RuleRepository
     */
    private $ruleRepository;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    private $filterProvider;

    /**
     * @var \Magento\Cms\Model\BlockRepository
     */
    private $blockRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Render constructor.
     * @param \Amasty\Groupcat\Model\ProductRuleProvider $ruleProvider
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Amasty\Groupcat\Helper\Data $helper
     * @param \Amasty\Groupcat\Model\RuleRepository $ruleRepository
     * @param \Magento\Cms\Model\BlockRepository $blockRepository
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Amasty\Groupcat\Model\ProductRuleProvider $ruleProvider,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Amasty\Groupcat\Helper\Data $helper,
        \Amasty\Groupcat\Model\RuleRepository $ruleRepository,
        \Magento\Cms\Model\BlockRepository\Proxy $blockRepository,
        \Magento\Cms\Model\Template\FilterProvider\Proxy $filterProvider,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->ruleProvider = $ruleProvider;
        $this->eventManager = $eventManager;
        $this->helper = $helper;
        $this->ruleRepository  = $ruleRepository;
        $this->filterProvider  = $filterProvider;
        $this->blockRepository = $blockRepository;
        $this->storeManager    = $storeManager;
    }

    /**
     * @param \Magento\Framework\Pricing\SaleableInterface $saleableItem
     * @param array $arguments
     *
     * @return bool
     */
    public function isNeedRenderPrice($saleableItem, $arguments)
    {
        $isNotProduct = !($saleableItem instanceof ProductInterface);
        // is current price block zone is not list or view
        $isNoZone = (key_exists('zone', $arguments)
            && !in_array($arguments['zone'], [PricingRender::ZONE_ITEM_LIST, PricingRender::ZONE_ITEM_VIEW]));

        $isShowPrice = !$this->helper->isModuleEnabled()
            || $isNotProduct
            || $isNoZone
            || !$this->ruleProvider->getProductPriceAction($saleableItem);

        $this->eventManager->dispatch(
            'amasty_groupcat_is_show_price',
            ['item' => $saleableItem, 'is_show_price' => &$isShowPrice]
        );

        return $isShowPrice;
    }

    /**
     * @param ProductInterface $product
     * @param string $zone
     * @return string
     */
    public function getPriceCmsBlockForZone($product, $zone)
    {
        $html = '';
        $ruleIndex = $this->ruleProvider->getRuleForProduct($product);
        $rule      = $this->ruleRepository->get($ruleIndex['rule_id']);
        $blockId   = null;
        switch ($zone) {
            case PricingRender::ZONE_ITEM_VIEW:
                $blockId = $rule->getBlockIdView();
                break;
            case PricingRender::ZONE_ITEM_LIST:
                $blockId = $rule->getBlockIdList();
                break;
        }
        try {
            $block = $this->blockRepository->getById($blockId);
            if ($block->isActive()) {
                $html = $this->filterProvider
                    ->getBlockFilter()
                    ->setStoreId($this->storeManager->getStore()->getId())
                    ->filter($block->getContent());
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            // if failed to load CMS entity then hide price.
        }

        return $html;
    }
}
