<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Plugin\Catalog\Model\ResourceModel;

use Amasty\ElasticSearch\Model\Indexer\RelevanceRule\RuleProductProcessor;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Amasty\ElasticSearch\Model\ResourceModel\RelevanceRule\CollectionFactory as RuleCollectionFactory;
use Amasty\ElasticSearch\Model\ResourceModel\RelevanceRule\Collection;
use Amasty\ElasticSearch\Model\RelevanceRule;
use Magento\CatalogRule\Model\Rule\Condition\Combine;
use Magento\Framework\Message\ManagerInterface;
use Magento\Rule\Model\Condition\Product\AbstractProduct;

class EavAttribute
{
    /**
     * @var RuleCollectionFactory
     */
    private $ruleCollectionFactory;

    /**
     * @var RuleProductProcessor
     */
    private $ruleProductProcessor;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    public function __construct(
        RuleCollectionFactory $ruleCollectionFactory,
        RuleProductProcessor $ruleProductProcessor,
        ManagerInterface $messageManager
    ) {
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->ruleProductProcessor = $ruleProductProcessor;
        $this->messageManager = $messageManager;
    }

    /**
     * @param Attribute $subject
     * @param Attribute $attribute
     * @return Attribute
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(Attribute $subject, Attribute $attribute)
    {
        if ($attribute->dataHasChangedFor('is_used_for_promo_rules') && !$attribute->getIsUsedForPromoRules()) {
            $this->checkRelativeRulesAvailability($attribute->getAttributeCode());
        }

        return $attribute;
    }

    /**
     * @param Attribute $subject
     * @param Attribute $attribute
     * @return Attribute
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(Attribute $subject, Attribute $attribute) {
        if ($attribute->getIsUsedForPromoRules()) {
            $this->checkRelativeRulesAvailability($attribute->getAttributeCode());
        }

        return $attribute;
    }

    /**
     * @param string $attributeCode
     * @return $this
     */
    private function checkRelativeRulesAvailability($attributeCode)
    {
        /* @var $collection Collection */
        $collection = $this->ruleCollectionFactory->create()->addAttributeInConditionFilter($attributeCode);
        $disabledRulesCount = 0;
        foreach ($collection as $rule) {
            /* @var $rule RelevanceRule */
            $rule->setIsEnabled(false);
            $this->removeAttributeFromConditions($rule->getCatalogRule()->getConditions(), $attributeCode);
            $rule->setConditions($rule->getConditionsSerialized());
            $rule->save();
            $disabledRulesCount++;
        }

        if ($disabledRulesCount) {
            $this->ruleProductProcessor->markIndexerAsInvalid();
            $this->messageManager->addWarning(
                __(
                    'You disabled %1 ElasticSearch Relevance Rules based on "%2" attribute.',
                    $disabledRulesCount,
                    $attributeCode
                )
            );
        }

        return $this;
    }

    /**
     * @param Combine $combine
     * @param string $attributeCode
     * @return void
     */
    private function removeAttributeFromConditions(Combine $combine, $attributeCode)
    {
        $conditions = $combine->getConditions();
        foreach ($conditions as $conditionId => $condition) {
            if ($condition instanceof Combine) {
                $this->removeAttributeFromConditions($condition, $attributeCode);
            }

            if ($condition instanceof AbstractProduct) {
                if ($condition->getAttribute() == $attributeCode) {
                    unset($conditions[$conditionId]);
                }
            }
        }

        $combine->setConditions($conditions);
    }
}
