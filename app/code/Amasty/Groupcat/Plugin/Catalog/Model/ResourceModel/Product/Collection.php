<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Plugin\Catalog\Model\ResourceModel\Product;

use Magento\Catalog\Model\Product;

class Collection
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
     * @var \Amasty\Groupcat\Model\ResourceModel\Rule
     */
    private $ruleResource;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * Collection constructor.
     *
     * @param \Amasty\Groupcat\Model\ProductRuleProvider $ruleProvider
     * @param \Amasty\Groupcat\Model\ResourceModel\Rule  $ruleResource
     * @param \Amasty\Groupcat\Helper\Data               $helper
     * @param \Magento\Framework\Registry                $coreRegistry
     */
    public function __construct(
        \Amasty\Groupcat\Model\ProductRuleProvider $ruleProvider,
        \Amasty\Groupcat\Model\ResourceModel\Rule $ruleResource,
        \Amasty\Groupcat\Helper\Data $helper,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->ruleProvider = $ruleProvider;
        $this->helper       = $helper;
        $this->ruleResource = $ruleResource;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $subject
     * @param                                                         $printQuery
     * @param                                                         $logQuery
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeLoad(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $subject,
        $printQuery = null,
        $logQuery = null
    ) {
        if (!$this->helper->isModuleEnabled()
            || $subject->getFlag('groupcat_filter_applied')
            || $subject->isLoaded()
            || $this->coreRegistry->registry('amasty_ignore_product_filter')
        ) {
            return;
        }
        $this->addRestrictedProductFilter($subject, $subject->getSelect());
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $subject
     * @param \Magento\Framework\DB\Select                            $productSelect
     *
     * @return \Magento\Framework\DB\Select
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSelect(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $subject,
        \Magento\Framework\DB\Select $productSelect
    ) {
        if (!$this->helper->isModuleEnabled()
            || $subject->getFlag('groupcat_filter_applied')
            || !count($productSelect->getPart($productSelect::FROM)) //avoid _initSelect
            || $this->coreRegistry->registry('amasty_ignore_product_filter')
        ) {
            return $productSelect;
        }
        $this->addRestrictedProductFilter($subject, $productSelect);

        return $productSelect;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $subject
     */
    public function beforeGetSize(\Magento\Catalog\Model\ResourceModel\Product\Collection $subject)
    {
        if (!$this->helper->isModuleEnabled()
            || $subject->getFlag('groupcat_filter_applied')
            || $this->coreRegistry->registry('amasty_ignore_product_filter')
        ) {
            return;
        }
        $this->addRestrictedProductFilter($subject, $subject->getSelect());
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $subject
     * @param \Magento\Framework\DB\Select                            $productSelect
     */
    protected function addRestrictedProductFilter(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $subject,
        \Magento\Framework\DB\Select $productSelect
    ) {
        $subject->setFlag('groupcat_filter_applied', 1);
        $productIds = $this->ruleProvider->getRestrictedProductIds();
        if ($productIds && $subject->getIdFieldName() == 'entity_id') {
            $idField = $subject::MAIN_TABLE_ALIAS . '.entity_id';
            $productSelect->where($idField . ' NOT IN (?)', $productIds);
        }
    }
}
