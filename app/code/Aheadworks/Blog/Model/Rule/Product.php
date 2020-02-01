<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Rule;

use Magento\CatalogRule\Model\Rule\Condition\CombineFactory;
use Magento\CatalogRule\Model\Rule\Action\CollectionFactory as ActionCollectionFactory;
use Aheadworks\Blog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Model\ResourceModel\Iterator as ResourceIterator;
use Magento\Catalog\Model\ProductFactory as ProductModelFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session;
use Magento\CatalogRule\Helper\Data as RuleHelperData;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product\Visibility as CatalogProductVisibility;

/**
 * Class Product
 *
 * @package Aheadworks\Blog\Model\Rule
 */
class Product extends \Magento\CatalogRule\Model\Rule
{
    /**
     * @var CombineFactory
     */
    private $combineFactory;

    /**
     * @var ActionCollectionFactory
     */
    private $actionCollectionFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param TimezoneInterface $localeDate
     * @param CombineFactory $combineFactory
     * @param ActionCollectionFactory $actionCollectionFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ProductModelFactory $productFactory
     * @param ResourceIterator $resourceIterator
     * @param StoreManagerInterface $storeManager
     * @param Session $customerSession
     * @param RuleHelperData $catalogRuleData
     * @param TypeListInterface $cacheTypesList
     * @param DateTime $dateTime
     * @param RuleProductProcessor $ruleProductProcessor
     * @param CollectionFactory $catalogProductCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        CombineFactory $combineFactory,
        ActionCollectionFactory $actionCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        ProductModelFactory $productFactory,
        ResourceIterator $resourceIterator,
        StoreManagerInterface $storeManager,
        Session $customerSession,
        RuleHelperData $catalogRuleData,
        TypeListInterface $cacheTypesList,
        DateTime $dateTime,
        RuleProductProcessor $ruleProductProcessor,
        CollectionFactory $catalogProductCollectionFactory,
        array $data = []
    ) {
        $this->combineFactory = $combineFactory;
        $this->actionCollectionFactory = $actionCollectionFactory;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $catalogProductCollectionFactory,
            $storeManager,
            $combineFactory,
            $actionCollectionFactory,
            $productFactory,
            $resourceIterator,
            $customerSession,
            $catalogRuleData,
            $cacheTypesList,
            $dateTime,
            $ruleProductProcessor,
            null,
            null,
            [],
            $data
        );
    }

    /**
     * Getter for rule conditions collection
     *
     * @return \Aheadworks\Blog\Model\Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->combineFactory->create();
    }

    /**
     * Getter for rule actions collection
     *
     * @return \Magento\CatalogRule\Model\Rule\Action\Collection
     */
    public function getActionsInstance()
    {
        return $this->actionCollectionFactory->create();
    }

    /**
     * Reset rule combine conditions
     *
     * @param \Aheadworks\Blog\Model\Rule\Condition\Combine|null $conditions
     * @return $this
     */
    protected function _resetConditions($conditions = null)
    {
        parent::_resetConditions($conditions);
        $this->getConditions($conditions)
            ->setId('1')
            ->setPrefix('conditions');
        return $this;
    }

    /**
     * Get array of product ids which are matched by rule
     *
     * @return array
     */
    public function getProductIds()
    {
        if ($this->_productIds === null) {
            $this->_productIds = [];
            $this->setCollectedAttributes([]);

            if ($this->getWebsiteIds()) {
                /** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
                $productCollection = $this->_productCollectionFactory->create();
                $productCollection->addWebsiteFilter($this->getWebsiteIds());
                $visibilityTypes = [
                    CatalogProductVisibility::VISIBILITY_BOTH,
                    CatalogProductVisibility::VISIBILITY_IN_CATALOG
                ];
                $productCollection->addFieldToFilter(
                    'visibility',
                    ['in' => implode(',', $visibilityTypes)]
                );
                if ($this->_productsFilter) {
                    $productCollection->addIdFilter($this->_productsFilter);
                }
                $this->getConditions()->collectValidatedAttributes($productCollection);

                $this->_resourceIterator->walk(
                    $productCollection->getSelect(),
                    [[$this, 'callbackValidateProduct']],
                    [
                        'attributes' => $this->getCollectedAttributes(),
                        'product' => $this->_productFactory->create()
                    ]
                );
            }
        }

        return $this->_productIds;
    }
}
