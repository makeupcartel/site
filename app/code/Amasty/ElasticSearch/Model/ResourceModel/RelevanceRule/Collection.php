<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\ResourceModel\RelevanceRule;

use Amasty\ElasticSearch\Api\Data\RelevanceRuleInterface;
use Amasty\ElasticSearch\Model\Indexer\RelevanceRule\IndexBuilder;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $serializer;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger, \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Amasty\Base\Model\Serializer $serializer,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_idFieldName = RelevanceRuleInterface::RULE_ID;
        $this->_init(
            \Amasty\ElasticSearch\Model\RelevanceRule::class,
            \Amasty\ElasticSearch\Model\ResourceModel\RelevanceRule::class
        );
    }

    /**
     * @return $this
     */
    public function addActiveFilter()
    {
        $this->getSelect()->where(RelevanceRuleInterface::IS_ENABLED . ' = 1');
        return $this;
    }

    /**
     * @param string $attributeCode
     * @return $this
     */
    public function addAttributeInConditionFilter($attributeCode)
    {
        $match = sprintf('%%%s%%', substr($this->serializer->serialize(['attribute' => $attributeCode]), 1, -1));
        $this->addFieldToFilter(RelevanceRuleInterface::CONDITIONS, ['like' => $match]);

        return $this;
    }

    /**
     * @param int[] $productIds
     * @param int $date
     * @param int $websiteId
     * @return array
     */
    public function getProductBoostMultipliers($productIds, $date, $websiteId)
    {
        $multiplier = RelevanceRuleInterface::MULTIPLIER;
        $boost = new \Zend_Db_Expr(
            "GREATEST(SUM(GREATEST($multiplier, 0)), 1) / GREATEST(ABS(SUM(LEAST(($multiplier), 0))), 1)"
        );
        $columns = [IndexBuilder::PRODUCT_ID => IndexBuilder::PRODUCT_ID, RelevanceRuleInterface::MULTIPLIER => $boost];
        $select = $this->getConnection()->select()
            ->from($this->getTable(IndexBuilder::TABLE_NAME), $columns)
            ->where(RelevanceRuleInterface::WEBSITE_ID . ' = ?', $websiteId)
            ->where(RelevanceRuleInterface::FROM_DATE . ' <= ?', $date)
            ->where(RelevanceRuleInterface::TO_DATE . ' >= ?', $date)
            ->group(IndexBuilder::PRODUCT_ID);
        if (count($productIds) < 100) {
            $select->where(IndexBuilder::PRODUCT_ID . ' IN(?)', $productIds);
        } else {
            $select->where(IndexBuilder::PRODUCT_ID . ' between ' . min($productIds) . ' AND ' . max($productIds));
        }

        return $this->getConnection()->fetchPairs($select);
    }
}
