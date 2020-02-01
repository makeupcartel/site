<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model;

use Amasty\ElasticSearch\Api\Data\RelevanceRuleInterface;
use Amasty\ElasticSearch\Api\RelevanceRuleRepositoryInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Amasty\ElasticSearch\Model\ResourceModel\RelevanceRule\CollectionFactory;

class RelevanceRuleRepository implements RelevanceRuleRepositoryInterface
{
    /**
     * @var RelevanceRuleFactory
     */
    private $ruleFactory;

    /**
     * @var ResourceModel\RelevanceRule
     */
    private $ruleResource;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    public function __construct(
        RelevanceRuleFactory $ruleFactory,
        ResourceModel\RelevanceRule $ruleResource,
        CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->ruleResource = $ruleResource;
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        $this->localeDate = $localeDate;
    }

    /**
     * @inheritdoc
     */
    public function save(RelevanceRuleInterface $rule)
    {
        try {
            if ($rule->getId()) {
                $rule = $this->get($rule->getId())->addData($rule->getData());
            }

            $this->ruleResource->save($rule);
        } catch (AlreadyExistsException $e) {
            throw new AlreadyExistsException(__($e->getMessage()));
        } catch (\Exception $e) {
            if ($rule->getStopWordId()) {
                throw new CouldNotSaveException(
                    __('Unable to save stopWord with ID %1. Error: %2', [$rule->Id(), $e->getMessage()])
                );
            }

            throw new CouldNotSaveException(__('Unable to save new Relevance Rule. Error: %1', $e->getMessage()));
        }

        return $rule;
    }

    /**
     * @inheritdoc
     */
    public function get($ruleId = null)
    {
        /** @var \Amasty\ElasticSearch\Model\RelevanceRule $rule */
        $rule = $this->ruleFactory->create();
        if ($ruleId !== null) {
            $this->ruleResource->load($rule, $ruleId);
            if (!$rule->getId()) {
                throw new NoSuchEntityException(__('Relevance Rule with specified ID "%1" not found.', $ruleId));
            }
        }

        return $rule;
    }

    /**
     * @inheritdoc
     */
    public function delete(RelevanceRuleInterface $rule)
    {
        try {
            $this->ruleResource->delete($rule);
        } catch (\Exception $e) {
            if ($rule->getId()) {
                throw new CouldNotDeleteException(
                    __('Unable to remove Relevance Rule with ID %1. Error: %2', [$rule->getId(), $e->getMessage()])
                );
            }

            throw new CouldNotDeleteException(__('Unable to remove Relevance Rule. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($ruleId)
    {
        $stopWordModel = $this->get($ruleId);
        $this->delete($stopWordModel);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getActiveRules()
    {
        return $this->collectionFactory->create()->addActiveFilter();
    }

    /**
     * @inheritdoc
     */
    public function getProductBoostMultipliers($productIds)
    {
        $website = $this->storeManager->getWebsite();
        $date = $this->localeDate->scopeTimeStamp($website->getDefaultStore()->getId());
        return $this->collectionFactory->create()->getProductBoostMultipliers($productIds, $date, $website->getId());
    }
}
