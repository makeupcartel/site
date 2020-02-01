<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Plugin\ConfigurableProduct\Block\Product\View\Type;

use Amasty\Groupcat\Model\Rule;

class Configurable
{
    /**
     * @var \Amasty\Groupcat\Model\ProductRuleProvider
     */
    private $ruleProvider;

    /**
     * @var \Amasty\Groupcat\Helper\Data
     */
    private $helper;

    /**
     * Configurable constructor.
     *
     * @param \Amasty\Groupcat\Helper\Data               $helper
     * @param \Amasty\Groupcat\Model\ProductRuleProvider $ruleProvider
     */
    public function __construct(
        \Amasty\Groupcat\Helper\Data $helper,
        \Amasty\Groupcat\Model\ProductRuleProvider $ruleProvider
    ) {
        $this->ruleProvider = $ruleProvider;
        $this->helper = $helper;
    }

    /**
     * Get Key for caching block content
     * For correct work of hide/show switcher on product list of different group rules
     *
     * @since 1.2.0 cache contains active rule ids instead customer group
     * @since 1.1.5 added customer group id to the key.
     *
     * @param \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject
     * @param string                                                            $value
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCacheKey(\Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject, $value)
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
