<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Model\Indexer\Category;

use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Search\Request\Dimension;

class IndexStructure implements IndexStructureInterface
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var IndexScopeResolver
     */
    private $indexScopeResolver;

    /**
     * IndexStructure constructor.
     * @param ResourceConnection $resource
     * @param IndexScopeResolver $indexScopeResolver
     */
    public function __construct(
        ResourceConnection $resource,
        IndexScopeResolver $indexScopeResolver
    ) {
        $this->resource = $resource;
        $this->indexScopeResolver = $indexScopeResolver;
    }

    /**
     * @param string $ind
     * @param array $dim
     */
    public function delete($ind, array $dim = [])
    {
        $table = $this->indexScopeResolver->resolve($ind, $dim);
        if ($this->resource->getConnection()->isTableExists($table)) {
            $this->resource->getConnection()->dropTable($table);
        }
    }

    /**
     * @param string $index
     * @param array $fields
     * @param array $dimensions
     * @throws \Zend_Db_Exception
     */
    public function create($index, array $fields, array $dimensions = [])
    {
        $this->createFulltextIndex($this->indexScopeResolver->resolve($index, $dimensions));
    }

    /**
     * @param $tableName
     * @throws \Zend_Db_Exception
     */
    protected function createFulltextIndex($tableName)
    {
        $table = $this->resource->getConnection()->newTable($tableName)
           ->addColumn(
               'entity_id',
               Table::TYPE_INTEGER,
               10,
               [
                   'unsigned' => true,
                   'nullable' => false
               ],
               'Entity ID'
           )->addColumn(
               'data_index',
               Table::TYPE_TEXT,
               '4g',
               ['nullable' => true],
               'Data index'
           )->addColumn(
               'attribute_id',
               Table::TYPE_INTEGER,
               10,
               [
                   'unsigned' => true,
                   'nullable' => false
               ]
           )->addIndex(
               'idx_primary',
               ['entity_id', 'attribute_id'],
               ['type' => AdapterInterface::INDEX_TYPE_PRIMARY]
           )->addIndex(
               'FTI_FULLTEXT_DATA_INDEX',
               ['data_index'],
               ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
           );

        $this->resource->getConnection()->createTable($table);
    }
}
