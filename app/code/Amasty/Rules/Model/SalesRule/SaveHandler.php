<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Model\SalesRule;

use Amasty\Rules\Api\Data\RuleInterface;
use Amasty\Rules\Api\Data\RuleInterfaceFactory;
use Amasty\Rules\Model\ResourceModel\Rule;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\SalesRule\Api\Data\RuleInterface as SalesRuleInterface;

/**
 * Class SaveHandler
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var Rule
     */
    protected $ruleResource;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var RuleInterfaceFactory
     */
    private $ruleFactory;

    public function __construct(
        Rule $ruleResource,
        MetadataPool $metadataPool,
        RuleInterfaceFactory $ruleFactory
    ) {
        $this->ruleResource = $ruleResource;
        $this->metadataPool = $metadataPool;
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * Stores Special Promotions Rule value from Sales Rule extension attributes
     *
     * @param \Magento\SalesRule\Model\Rule|\Magento\SalesRule\Model\Data\Rule $entity
     * @param array $arguments
     * @return \Magento\SalesRule\Model\Rule|\Magento\SalesRule\Model\Data\Rule
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $linkField = $this->metadataPool->getMetadata(SalesRuleInterface::class)->getLinkField();
        $attributes = $entity->getExtensionAttributes() ?: [];
        if (isset($attributes[RuleInterface::EXTENSION_CODE])) {
            $ruleLinkId = $entity->getDataByKey($linkField);
            $inputData = $attributes[RuleInterface::EXTENSION_CODE];
            /** @var \Amasty\Rules\Model\Rule $amRule */
            $amRule = $this->ruleFactory->create();
            $this->ruleResource->load($amRule, $ruleLinkId, RuleInterface::KEY_SALESRULE_ID);
            if ($inputData instanceof RuleInterface) {
                $amRule->addData($inputData->getData());
            } else {
                $amRule->addData($inputData);
            }

            if ($amRule->getSalesruleId() != $ruleLinkId) {
                $amRule->setEntityId(null);
                $amRule->setSalesruleId($ruleLinkId);
            }
            $this->ruleResource->save($amRule);
        }

        return $entity;
    }
}
