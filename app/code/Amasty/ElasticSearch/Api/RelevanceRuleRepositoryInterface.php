<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Api;

/**
 * @api
 */
interface RelevanceRuleRepositoryInterface
{
    /**
     * @param Data\RelevanceRuleInterface $rule
     * @return Data\SynonymInterface
     */
    public function save(Data\RelevanceRuleInterface $rule);

    /**
     * @param int $ruleId|null
     * @return Data\RelevanceRuleInterface
     */
    public function get($ruleId);

    /**
     * @param Data\RelevanceRuleInterface $rule
     * @return bool true on success
     */
    public function delete(Data\RelevanceRuleInterface $rule);

    /**
     * @param int $ruleId
     * @return bool true on success
     */
    public function deleteById($ruleId);

    /**
     * @return \Amasty\ElasticSearch\Model\ResourceModel\RelevanceRule\Collection
     */
    public function getActiveRules();

    /**
     * @param int[] $productIds
     * @return array
     */
    public function getProductBoostMultipliers($productIds);
}
