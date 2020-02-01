<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

namespace Amasty\Groupcat\Model;

use Amasty\Groupcat\Api\Data;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;

class RuleRepository implements \Amasty\Groupcat\Api\RuleRepositoryInterface
{
    /**
     * @var ResourceModel\Rule
     */
    protected $ruleResource;

    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var Indexer\Rule\RuleProductProcessor
     */
    protected $ruleIndexProcessor;

    /**
     * @var array
     */
    protected $rules = [];

    /**
     * RuleRepository constructor.
     *
     * @param ResourceModel\Rule                $ruleResource
     * @param RuleFactory                       $ruleFactory
     * @param Indexer\Rule\RuleProductProcessor $ruleIndexProcessor
     */
    public function __construct(
        \Amasty\Groupcat\Model\ResourceModel\Rule $ruleResource,
        \Amasty\Groupcat\Model\RuleFactory $ruleFactory,
        \Amasty\Groupcat\Model\Indexer\Rule\RuleProductProcessor $ruleIndexProcessor
    ) {
        $this->ruleResource = $ruleResource;
        $this->ruleFactory = $ruleFactory;
        $this->ruleIndexProcessor = $ruleIndexProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Data\RuleInterface $rule)
    {
        if ($rule->getRuleId()) {
            $rule = $this->get($rule->getRuleId())->addData($rule->getData());
        }

        try {
            $this->ruleResource->save($rule);
            unset($this->rules[$rule->getId()]);
        } catch (\Exception $e) {
            if ($rule->getRuleId()) {
                throw new CouldNotSaveException(
                    __('Unable to save rule with ID %1. Error: %2', [$rule->getRuleId(), $e->getMessage()])
                );
            }
            throw new CouldNotSaveException(__('Unable to save new rule. Error: %1', $e->getMessage()));
        }
        if ($this->ruleIndexProcessor->isIndexerScheduled()) {
            $this->ruleIndexProcessor->markIndexerAsInvalid();
        } else {
            $this->ruleIndexProcessor->reindexRow($rule->getId());
        }
        return $rule;
    }

    /**
     * {@inheritdoc}
     */
    public function get($ruleId)
    {
        if (!isset($this->rules[$ruleId])) {
            /** @var \Amasty\Groupcat\Model\Rule $rule */
            $rule = $this->ruleResource->load($this->ruleFactory->create(), $ruleId);
            if (!$rule->getRuleId()) {
                throw new NoSuchEntityException(__('Rule with specified ID "%1" not found.', $ruleId));
            }
            $this->rules[$ruleId] = $rule;
        }
        return $this->rules[$ruleId];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Data\RuleInterface $rule)
    {
        try {
            $this->ruleResource->delete($rule);
            unset($this->rules[$rule->getId()]);
        } catch (\Exception $e) {
            if ($rule->getRuleId()) {
                throw new CouldNotDeleteException(
                    __('Unable to remove rule with ID %1. Error: %2', [$rule->getRuleId(), $e->getMessage()])
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove rule rule. Error: %1', $e->getMessage()));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($ruleId)
    {
        $model = $this->get($ruleId);
        $this->delete($model);
        return true;
    }
}
