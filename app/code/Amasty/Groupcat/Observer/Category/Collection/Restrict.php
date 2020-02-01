<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Observer\Category\Collection;

use Magento\Framework\Event\ObserverInterface;

/**
 * observer for event catalog_category_collection_load_before
 */
class Restrict implements ObserverInterface
{
    use \Amasty\Groupcat\Observer\CatalogCollectionTrait;

    /**
     * @var \Amasty\Groupcat\Model\ProductRuleProvider
     */
    private $ruleProvider;

    /**
     * @var \Amasty\Groupcat\Helper\Data
     */
    private $helper;

    /**
     * Restrict constructor.
     *
     * @param \Amasty\Groupcat\Model\ProductRuleProvider                  $ruleProvider
     * @param \Amasty\Groupcat\Helper\Data                                $helper
     */
    public function __construct(
        \Amasty\Groupcat\Model\ProductRuleProvider $ruleProvider,
        \Amasty\Groupcat\Helper\Data $helper
    ) {
        $this->ruleProvider = $ruleProvider;
        $this->helper       = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->isModuleEnabled()) {
            $this->restrictCollectionIds(
                $observer->getEvent()->getCategoryCollection(),
                $this->ruleProvider->getRestrictCategoriesId()
            );
        }
    }
}
