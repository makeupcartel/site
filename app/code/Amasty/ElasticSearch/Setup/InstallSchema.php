<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $this->createSynonymTable($installer);
        $this->createStopWordTable($installer);

        $installer->endSetup();
    }

    /**
     * @param SchemaSetupInterface $installer
     * @throws \Zend_Db_Exception
     */
    private function createStopWordTable(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()->newTable(
            $installer->getTable('amasty_elastic_stop_word')
        )->addColumn(
            'stop_word_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unique' => true,  'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Stop Word Id'
        )->addColumn(
            'term',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false,  'unique' => true],
            'Stop Word'
        )->addColumn(
            'store_id',
            Table::TYPE_INTEGER,
            11,
            ['nullable' => false],
            'Store Id'
        )->addIndex(
            $installer->getIdxName('amasty_elastic_stop_word', ['term']),
            ['term']
        )->addIndex(
            $installer->getIdxName(
                'amasty_elastic_stop_word',
                ['term', 'store_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['term', 'store_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->setComment(
            'Stop Words Data'
        );

        $installer->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $installer
     * @throws \Zend_Db_Exception
     */
    private function createSynonymTable(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()
            ->newTable($installer->getTable('amasty_elastic_synonym'))
            ->addColumn(
                'synonym_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Synonym Id'
            )->addColumn(
                'store_id',
                Table::TYPE_INTEGER,
                11,
                ['nullable' => false],
                'Store Id'
            )->addColumn(
                'term',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false,  'unique' => true],
                'Term'
            )->addIndex(
                $installer->getIdxName('amasty_elastic_synonym', ['term']),
                ['term']
            )->addIndex(
                $installer->getIdxName(
                    'amasty_elastic_synonym',
                    ['term', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['term', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->setComment(
                'Terms and Synonyms Data'
            );

        $installer->getConnection()->createTable($table);
    }
}
