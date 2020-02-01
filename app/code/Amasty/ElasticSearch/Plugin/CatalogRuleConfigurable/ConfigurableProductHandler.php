<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Plugin\CatalogRuleConfigurable;

use Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\Rule\ConfigurableProductHandler
    as CatalogRuleConfigurableHandler;

class ConfigurableProductHandler
{
    /**
     * @var CatalogRuleConfigurableHandler
     */
    private $parentHandler;

    public function __construct(
        CatalogRuleConfigurableHandler $parentHandler
    ) {
        $this->parentHandler = $parentHandler;
    }

    /**
     * @param \Magento\CatalogRule\Model\Rule $rule
     * @param array $productIds
     * @return array
     */
    public function afterGetMatchingProductIds(\Magento\CatalogRule\Model\Rule $rule, array $productIds)
    {
        if (!$rule->getAmastyRelevanceRule()) {
            $productIds = $this->parentHandler->afterGetMatchingProductIds($rule, $productIds);
        }

        return $productIds;
    }
}
