<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Setup\Operation;

use Amasty\ElasticSearch\Api\Data\RelevanceRuleInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Store\Model\Website;

/**
 * Class ChangeRelevanceRuleTableRelation
 * @package Amasty\ElasticSearch\Setup\Operation
 */
class ChangeRelevanceRuleTableRelation
{
    /**
     * @param SchemaSetupInterface $installer
     * @throws \Exception
     */
    public function execute(SchemaSetupInterface $installer)
    {
        $connection = $installer->getConnection();
        $relevanceRuleTable = $installer->getTable(RelevanceRuleInterface::TABLE_NAME);
        $connection->dropForeignKey(
            $relevanceRuleTable,
            $installer->getFkName(
                RelevanceRuleInterface::TABLE_NAME,
                RelevanceRuleInterface::WEBSITE_ID,
                $installer->getTable(\Magento\Store\Model\Store::ENTITY),
                \Magento\Store\Model\Store::STORE_ID
            )
        )->addForeignKey(
            $installer->getFkName(
                RelevanceRuleInterface::TABLE_NAME,
                RelevanceRuleInterface::WEBSITE_ID,
                $installer->getTable(Website::ENTITY),
                RelevanceRuleInterface::WEBSITE_ID
            ),
            $relevanceRuleTable,
            RelevanceRuleInterface::WEBSITE_ID,
            $installer->getTable(Website::ENTITY),
            RelevanceRuleInterface::WEBSITE_ID,
            Table::ACTION_CASCADE
        );
    }
}
