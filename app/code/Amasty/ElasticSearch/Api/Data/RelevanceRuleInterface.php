<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Api\Data;

interface RelevanceRuleInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const RULE_ID = 'rule_id';
    const WEBSITE_ID = 'website_id';
    const IS_ENABLED = 'is_enabled';
    const TITLE = 'title';
    const FROM_DATE = 'from_date';
    const TO_DATE = 'to_date';
    const MULTIPLIER = 'multiplier';
    const CONDITIONS = 'conditions_serialized';
    const TABLE_NAME = 'amasty_elastic_relevance_rule';
    /**#@-*/

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return int
     */
    public function getWebsiteId();

    /**
     * @return bool
     */
    public function isEnabled();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return string
     */
    public function getFromDate();

    /**
     * @return string
     */
    public function getToDate();

    /**
     * @return int
     */
    public function getMultiplier();

    /**
     * @return string
     */
    public function getConditions();

    /**
     * @param int $websiteId
     * @return RelevanceRuleInterface
     */
    public function setWebsiteId($websiteId);

    /**
     * @param $isEnabled
     * @return RelevanceRuleInterface
     */
    public function setIsEnabled($isEnabled);

    /**
     * @param $title
     * @return RelevanceRuleInterface
     */
    public function setTitle($title);

    /**
     * @param $fromDate
     * @return RelevanceRuleInterface
     */
    public function setFromDate($fromDate);

    /**
     * @param $toDate
     * @return RelevanceRuleInterface
     */
    public function setToDate($toDate);

    /**
     * @param $multiplier
     * @return RelevanceRuleInterface
     */
    public function setMultiplier($multiplier);

    /**
     * @param $condition
     * @return RelevanceRuleInterface
     */
    public function setConditions($condition);

    /**
     * @return \Magento\CatalogRule\Model\Rule
     */
    public function getCatalogRule();

    /**
     * @return bool
     */
    public function isConditionEmpty();

}
