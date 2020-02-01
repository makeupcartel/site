<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Model\Indexer\Category;

use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;

class IndexerHandler implements IndexerInterface
{
    /**
     * @var IndexStructure
     */
    private $indexStructure;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var Batch
     */
    private $batch;

    /**
     * @var IndexScopeResolver
     */
    private $indexScopeResolver;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var array
     */
    private $data;

    /**
     * @var array
     */
    private $fields;

    /**
     * @var int
     */
    private $batchSize;

    public function __construct(
        IndexStructure $indexStructure,
        ResourceConnection $resource,
        Batch $batch,
        IndexScopeResolver $indexScopeResolver,
        Config $eavConfig,
        array $data,
        $batchSize = 100
    ) {
        $this->batchSize = $batchSize;
        $this->indexStructure = $indexStructure;
        $this->resource = $resource;
        $this->batch = $batch;
        $this->indexScopeResolver = $indexScopeResolver;
        $this->eavConfig = $eavConfig;
        $this->fields = [];
        $this->data = $data;

        $this->prepareFields();

    }

    public function saveIndex($dimensions, \Traversable $documents)
    {
        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            $this->insertDocuments($batchDocuments, $dimensions);
        }
    }

    public function deleteIndex($dimensions, \Traversable $documents)
    {
        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            $this->resource->getConnection()
                ->delete($this->getTableName($dimensions), ['entity_id in (?)' => $batchDocuments]);
        }
    }

    protected function insertDocuments(array $documents, array $dimensions)
    {
        $documents = $this->prepareSearchableFields($documents);
        if (empty($documents)) {
            return;
        }
        $this->resource->getConnection()->insertOnDuplicate(
            $this->getTableName($dimensions),
            $documents,
            ['data_index']
        );
    }

    public function cleanIndex($dimensions)
    {
        $this->indexStructure->delete($this->getIndexName(), $dimensions);
        $this->indexStructure->create($this->getIndexName(), [], $dimensions);
    }

    protected function getTableName($dimensions)
    {
        return $this->indexScopeResolver->resolve($this->getIndexName(), $dimensions);
    }

    protected function getIndexName()
    {
        return $this->data['indexer_id'];
    }

    protected function prepareSearchableFields(array $documents)
    {
        $insertDocuments = [];
        foreach ($documents as $entityId => $document) {
            foreach ($document as $attributeId => $fieldValue) {
                $insertDocuments[$entityId . '_' . $attributeId] = [
                    'entity_id' => $entityId,
                    'attribute_id' => $attributeId,
                    'data_index' => $fieldValue,
                ];
            }
        }

        return $insertDocuments;
    }

    public function isAvailable($dimensions = [])
    {
        return true;
    }

    protected function prepareFields()
    {
        foreach ($this->data['fieldsets'] as $fieldset) {
            $this->fields = array_merge($this->fields, $fieldset['fields']);
        }
    }
}
