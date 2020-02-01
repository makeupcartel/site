<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Setup;

use Amasty\ElasticSearch\Api\Data\RelevanceRuleInterface;
use Amasty\ElasticSearch\Model\Indexer\RelevanceRule\IndexBuilder;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var Operation\ChangeRelevanceRuleTableRelation
     */
    private $changeRelevanceRuleTableRelation;

    public function __construct(
        Operation\ChangeRelevanceRuleTableRelation $changeRelevanceRuleTableRelation
    )
    {
        $this->changeRelevanceRuleTableRelation = $changeRelevanceRuleTableRelation;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.3.0', '<')) {
            $this->addRelevanceRule($setup);
        }

        if (version_compare($context->getVersion(), '1.3.6', '<')) {
            $this->changeRelevanceRuleTableRelation->execute($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $installer
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function addRelevanceRule(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()->newTable(
            $installer->getTable(RelevanceRuleInterface::TABLE_NAME)
        )->addColumn(
            RelevanceRuleInterface::RULE_ID,
            Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'unique' => true,  'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Relevance Rule Id'
        )->addColumn(
            RelevanceRuleInterface::IS_ENABLED,
            Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false, 'default' => '1'],
            'Is Rule Enabled'
        )->addColumn(
            RelevanceRuleInterface::TITLE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Rule Title'
        )->addColumn(
            RelevanceRuleInterface::WEBSITE_ID,
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'unsigned' => true],
            'Website Id'
        )->addColumn(
            RelevanceRuleInterface::FROM_DATE,
            Table::TYPE_DATE,
            null,
            ['nullable' => true],
            'Is Enabled From Date'
        )->addColumn(
            RelevanceRuleInterface::TO_DATE,
            Table::TYPE_DATE,
            null,
            ['nullable' => true],
            'Is Enabled to Date'
        )->addColumn(
            RelevanceRuleInterface::MULTIPLIER,
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1'],
            'Relevance Multiplier'
        )->addColumn(
            RelevanceRuleInterface::CONDITIONS,
            Table::TYPE_TEXT,
            '',
            [],
            'Relevance Rule Conditions'
        )->addIndex(
            $installer->getIdxName(RelevanceRuleInterface::TABLE_NAME, [RelevanceRuleInterface::IS_ENABLED]),
            [RelevanceRuleInterface::IS_ENABLED]
        )->addIndex(
            $installer->getIdxName(RelevanceRuleInterface::TABLE_NAME, [RelevanceRuleInterface::WEBSITE_ID]),
            [RelevanceRuleInterface::WEBSITE_ID]
        )->addForeignKey(
            $installer->getFkName(
                RelevanceRuleInterface::TABLE_NAME,
                RelevanceRuleInterface::WEBSITE_ID,
                'store',
                'store_id'
            ),
            RelevanceRuleInterface::WEBSITE_ID,
            $installer->getTable('store'),
            'store_id',
            Table::ACTION_CASCADE
        )->setComment('Relevance Rules');

        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
            ->newTable($installer->getTable(IndexBuilder::TABLE_NAME))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )->addColumn(
                RelevanceRuleInterface::RULE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Rule Id'
            )->addColumn(
                IndexBuilder::PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Product Id'
            )->addColumn(
                RelevanceRuleInterface::WEBSITE_ID,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Website Id'
            )->addColumn(
                RelevanceRuleInterface::FROM_DATE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'From Time'
            )->addColumn(
                RelevanceRuleInterface::TO_DATE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'To time'
            )->addColumn(
                RelevanceRuleInterface::MULTIPLIER,
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '1'],
                'Relevance Multiplier'
            )->addIndex(
                $installer->getIdxName(
                    IndexBuilder::TABLE_NAME,
                    [
                        RelevanceRuleInterface::RULE_ID, IndexBuilder::PRODUCT_ID,
                        RelevanceRuleInterface::WEBSITE_ID, RelevanceRuleInterface::FROM_DATE,
                        RelevanceRuleInterface::TO_DATE, RelevanceRuleInterface::MULTIPLIER
                    ],
                    true
                ),
                [
                    RelevanceRuleInterface::RULE_ID, IndexBuilder::PRODUCT_ID,
                    RelevanceRuleInterface::WEBSITE_ID, RelevanceRuleInterface::FROM_DATE,
                    RelevanceRuleInterface::TO_DATE, RelevanceRuleInterface::MULTIPLIER
                ],
                ['type' => 'unique']
            )->addIndex(
                $installer->getIdxName(IndexBuilder::TABLE_NAME, [IndexBuilder::PRODUCT_ID]),
                [IndexBuilder::PRODUCT_ID]
            )->addIndex(
                $installer->getIdxName(IndexBuilder::TABLE_NAME, [RelevanceRuleInterface::WEBSITE_ID]),
                [RelevanceRuleInterface::WEBSITE_ID]
            )->addIndex(
                $installer->getIdxName(IndexBuilder::TABLE_NAME, [RelevanceRuleInterface::FROM_DATE]),
                [RelevanceRuleInterface::FROM_DATE]
            )->addIndex(
                $installer->getIdxName(IndexBuilder::TABLE_NAME, [RelevanceRuleInterface::TO_DATE]),
                [RelevanceRuleInterface::TO_DATE]
            )->setComment('Relevance Rule Product');

        $installer->getConnection()->createTable($table);
    }
}
