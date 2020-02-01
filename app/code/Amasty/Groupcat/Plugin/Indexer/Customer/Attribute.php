<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

namespace Amasty\Groupcat\Plugin\Indexer\Customer;

use Amasty\Groupcat\Model\Indexer\Rule\RuleProductProcessor;
use Amasty\Groupcat\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Amasty\Groupcat\Model\Rule;
use Amasty\Groupcat\Model\Rule\Condition\Customer\Combine;
use Magento\Framework\Message\ManagerInterface;
use Amasty\Groupcat\Model\Rule\Condition\Customer as CustomerCondition;

class Attribute
{
    /**
     * @var RuleCollectionFactory
     */
    protected $ruleCollectionFactory;

    /**
     * @var RuleProductProcessor
     */
    protected $ruleIndexProcessor;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @param RuleCollectionFactory $ruleCollectionFactory
     * @param RuleProductProcessor $ruleIndexProcessor
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        RuleCollectionFactory $ruleCollectionFactory,
        RuleProductProcessor $ruleIndexProcessor,
        ManagerInterface $messageManager
    ) {
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->ruleIndexProcessor    = $ruleIndexProcessor;
        $this->messageManager        = $messageManager;
    }

    /**
     * @param \Magento\Customer\Model\ResourceModel\Attribute $subject
     * @param callable                                        $proceed
     * @param \Magento\Customer\Model\Attribute               $attribute
     *
     * @return \Magento\Customer\Model\ResourceModel\Attribute
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDelete(
        \Magento\Customer\Model\ResourceModel\Attribute $subject,
        callable $proceed,
        \Magento\Customer\Model\Attribute $attribute
    ) {
        $attributeCode = $attribute->getAttributeCode();
        $result = $proceed($attribute);
        $this->checkRulesAvailability($attributeCode);
        return $result;
    }

    /**
     * Check rules that contains affected attribute
     * If rules were found they will be set to inactive and notice will be add to admin session
     *
     * @param string $attributeCode
     * @return $this
     */
    protected function checkRulesAvailability($attributeCode)
    {
        /** @var $collection \Amasty\Groupcat\Model\ResourceModel\Rule\Collection */
        $collection = $this->ruleCollectionFactory->create()->addAttributeInActionFilter($attributeCode);

        $disabledRulesCount = 0;
        foreach ($collection as $rule) {
            /** @var $rule Rule */
            $rule->setIsActive(0);
            /** @var $rule->getConditions() Combine */
            $this->removeAttributeFromConditions($rule->getActions(), $attributeCode);
            $rule->save();

            $disabledRulesCount++;
        }

        if ($disabledRulesCount) {
            $this->ruleIndexProcessor->markIndexerAsInvalid();
            $this->messageManager->addWarningMessage(
                __(
                    'You disabled %1 Amasty Customer Group Catalog Rules based on "%2" attribute.',
                    $disabledRulesCount,
                    $attributeCode
                )
            );
        }

        return $this;
    }

    /**
     * Remove customer attribute condition by attribute code from rule conditions
     *
     * @param Combine $combine
     * @param string $attributeCode
     * @return void
     */
    protected function removeAttributeFromConditions(Combine $combine, $attributeCode)
    {
        $conditions = $combine->getActions();
        foreach ($conditions as $conditionId => $condition) {
            if ($condition instanceof Combine) {
                $this->removeAttributeFromConditions($condition, $attributeCode);
            }
            if ($condition instanceof CustomerCondition) {
                if ($condition->getAttribute() == $attributeCode) {
                    unset($conditions[$conditionId]);
                }
            }
        }
        $combine->setActions($conditions);
    }
}
