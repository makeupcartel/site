<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Plugin\Catalog\Model;

class Layer
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
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * Restrict constructor.
     *
     * @param \Amasty\Groupcat\Model\ProductRuleProvider                  $ruleProvider
     * @param \Amasty\Groupcat\Helper\Data                                $helper
     */
    public function __construct(
        \Amasty\Groupcat\Model\ProductRuleProvider $ruleProvider,
        \Amasty\Groupcat\Helper\Data $helper,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->ruleProvider = $ruleProvider;
        $this->helper       = $helper;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Prepare Product Collection for layred Navigation.
     * Add restricted product filter to search engine.
     * In search_request.xml added filter for amasty_groupcat_entity_id
     *
     * @param \Magento\Catalog\Model\Layer                            $subject
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     *
     * @return array|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforePrepareProductCollection($subject, $collection)
    {
        if (!$this->helper->isModuleEnabled() || $this->coreRegistry->registry('amasty_ignore_product_filter')) {
            return null;
        }
        $collection->setFlag('groupcat_filter_applied', 1);
        $productIds = $this->ruleProvider->getRestrictedProductIds();
        if ($productIds) {
            // add filter to product fulltext search | catalog product collection
            $collection->addFieldToFilter('amasty_groupcat_entity_id', ['nin' => $productIds]);

            return [$collection];
        }

        return null;
    }
}
