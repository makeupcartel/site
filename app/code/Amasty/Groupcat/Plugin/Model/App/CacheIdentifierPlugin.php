<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

namespace Amasty\Groupcat\Plugin\Model\App;

use Amasty\Groupcat\Model\Rule;

/**
 * Plugin change cache key to show correct pages when customer rules applies
 */
class CacheIdentifierPlugin
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
     * CacheIdentifierPlugin constructor.
     * @param \Amasty\Groupcat\Helper\Data $helper
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
     * @param \Magento\Framework\App\PageCache\Identifier $identifier
     * @param string $result
     * @return string
     */
    public function afterGetValue(\Magento\Framework\App\PageCache\Identifier $identifier, $result)
    {
        if ($this->helper->isModuleEnabled() && strpos($result, Rule::CACHE_TAG) === false) {
            $ruleCollection = $this->ruleProvider->getActiveRulesCollection();
            $activeRulesIds = $ruleCollection->getAllIds();
            $key = Rule::CACHE_TAG;
            if (!empty($activeRulesIds)) {
                $result = $result . $key . implode('_', $activeRulesIds);
            }
        }

        return $result;
    }
}
