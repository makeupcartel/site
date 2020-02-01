<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

namespace Amasty\Groupcat\Plugin\Theme\Block\Html;

use Amasty\Groupcat\Model\Rule;

/**
 * Plugin for top menu block
 */
class Topmenu
{
    /**
     * @var \Amasty\Groupcat\Model\ProductRuleProvider
     */
    private $ruleProvider;

    /**
     * @var \Amasty\Groupcat\Helper\Data
     */
    private $helper;

    public function __construct(
        \Amasty\Groupcat\Helper\Data $helper,
        \Amasty\Groupcat\Model\ProductRuleProvider $ruleProvider
    ) {
        $this->ruleProvider = $ruleProvider;
        $this->helper = $helper;
    }

    /**
     * Get Key for caching block content
     *
     * @param \Magento\Theme\Block\Html\Topmenu $subject
     * @param string                            $value
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCacheKey(\Magento\Theme\Block\Html\Topmenu $subject, $value)
    {
        if ($this->helper->isModuleEnabled() && strpos($value, Rule::CACHE_TAG) === false) {
            $ruleCollection = $this->ruleProvider->getActiveRulesCollection();
            $ruleCollection->addFieldToFilter('hide_category', 1);
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
