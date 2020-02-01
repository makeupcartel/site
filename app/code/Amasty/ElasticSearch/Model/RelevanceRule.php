<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model;

use Amasty\ElasticSearch\Api\Data\RelevanceRuleInterface;

class RelevanceRule extends \Magento\Framework\Model\AbstractModel implements RelevanceRuleInterface
{
    /**
     * @var \Magento\CatalogRule\Model\RuleFactory
     */
    private $catalogRuleFactory;

    /**
     * @var \Magento\CatalogRule\Model\Rule
     */
    private $catalogRule;

    /**
     * @var Indexer\RelevanceRule\RuleProductProcessor
     */
    private $ruleProductProcessor;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->catalogRuleFactory = $this->getData('catalogrule_factory');
        $this->ruleProductProcessor = $this->getData('rule_product_processor');
        $this->_init(ResourceModel\RelevanceRule::class);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getData(self::RULE_ID);
    }

    /**
     * @inheritdoc
     */
    public function getWebsiteId()
    {
        return (int)$this->getData(self::WEBSITE_ID);
    }

    /**
     * @inheritdoc
     */
    public function isEnabled()
    {
        return (bool)$this->getData(self::IS_ENABLED);
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }


    /**
     * @inheritdoc
     */
    public function getFromDate()
    {
        return $this->getData(self::FROM_DATE);
    }

    /**
     * @inheritdoc
     */
    public function getToDate()
    {
        return $this->getData(self::TO_DATE);
    }

    /**
     * @inheritdoc
     */
    public function getMultiplier()
    {
        return $this->getData(self::MULTIPLIER);
    }

    /**
     * @inheritdoc
     */
    public function getConditions()
    {
        return $this->getData(self::CONDITIONS);
    }

    /**
     * @inheritdoc
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }

    /**
     * @inheritdoc
     */
    public function setIsEnabled($isEnabled)
    {
        return $this->setData(self::IS_ENABLED, $isEnabled);
    }

    /**
     * @inheritdoc
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * @inheritdoc
     */
    public function setFromDate($fromDate)
    {
        return $this->setData(self::FROM_DATE, $fromDate);
    }

    /**
     * @inheritdoc
     */
    public function setToDate($toDate)
    {
        return $this->setData(self::TO_DATE, $toDate);
    }

    /**
     * @inheritdoc
     */
    public function setMultiplier($multiplier)
    {
        return $this->setData(self::MULTIPLIER, $multiplier);
    }

    /**
     * @inheritdoc
     */
    public function setConditions($condition)
    {
        return $this->setData(self::CONDITIONS, $condition);
    }

    /**
     * @inheritdoc
     */
    public function getCatalogRule()
    {
        if (!$this->catalogRule) {
            $this->catalogRule = $this->catalogRuleFactory->create()
                ->setConditionsSerialized($this->getConditions())
                ->setWebsiteIds([$this->getWebsiteId()])
                ->setAmastyRelevanceRule($this);
        }

        return $this->catalogRule;
    }

    /**
     * @param array|null $conditions
     * @return string
     */
    public function getConditionsSerialized(array $conditions = null)
    {
        if ($conditions === null) {
            $catalogRule = $this->getCatalogRule();
        } else {
            $catalogRule =  $this->catalogRuleFactory->create()->loadPost(['conditions' => $conditions]);
        }

        return $catalogRule->beforeSave()->getConditionsSerialized();
    }

    /**
     * @return bool
     */
    public function isConditionEmpty()
    {
        $conditions = $this->getCatalogRule()->getConditions()->asArray();
        return !isset($conditions['conditions']);
    }

    /**
     * @inheritdoc
     */
    public function afterSave()
    {
        if ($this->isObjectNew() || !$this->ruleProductProcessor->isIndexerScheduled()) {
            $this->_getResource()->addCommitCallback([$this, 'reindex']);
        } else {
            $this->ruleProductProcessor->getIndexer()->invalidate();
        }
        return parent::afterSave();
    }

    /**
     * @inheritdoc
     */
    public function reindex()
    {
        $this->ruleProductProcessor->reindexRow($this->getId());
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        if ($this->ruleProductProcessor->isIndexerScheduled()) {
            $this->ruleProductProcessor->getIndexer()->invalidate();
        } else {
            $this->ruleProductProcessor->reindexRow($this->getId());
        }

        return parent::afterDelete();
    }
}
