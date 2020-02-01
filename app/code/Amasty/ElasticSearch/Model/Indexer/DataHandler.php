<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Indexer;

use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Magento\Framework\Indexer\IndexStructureInterface;
use Amasty\ElasticSearch\Model\Client\Elasticsearch as Client;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Search\Request\Dimension;

class DataHandler implements IndexerInterface
{
    /**
     * Default batch size
     */
    const BATCH_SIZE = 1000;

    /**
     * @var IndexStructureInterface
     */
    private $indexStructure;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Batch
     */
    private $batch;

    /**
     * @var array
     */
    private $data;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    public function __construct(
        IndexStructureInterface $indexStructure,
        Client $client,
        Batch $batch,
        ScopeResolverInterface $scopeResolver,
        array $data = [],
        $batchSize = self::BATCH_SIZE
    ) {
        $this->indexStructure = $indexStructure;
        $this->client = $client;
        $this->batch = $batch;
        $this->data = $data;
        $this->batchSize = $batchSize;
        $this->scopeResolver = $scopeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function saveIndex($dimensions, \Traversable $documents)
    {
        $scopeId = $this->resolveScopeId($dimensions);
        foreach ($this->batch->getItems($documents, $this->batchSize) as $documents) {
            $documents = $this->getClient()->prepareDocuments($documents, $scopeId, $this->getIndexerId());
            $this->getClient()->saveDocuments($documents, $scopeId, $this->getIndexerId());
        }

        $this->getClient()->updateAlias($this->getIndexerId(), $scopeId);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteIndex($dimensions, \Traversable $documents)
    {
        $scopeId = $this->resolveScopeId($dimensions);
        $documentIds = [];
        foreach ($documents as $document) {
            $documentIds[$document] = $document;
        }
        $this->getClient()->deleteDocuments($documentIds, $scopeId, $this->getIndexerId());
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function cleanIndex($dimensions)
    {
        $this->indexStructure->delete($this->getIndexerId(), $dimensions);
        $this->indexStructure->create($this->getIndexerId(), [], $dimensions);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailable($dimensions = [])
    {
        return $this->getClient()->ping();
    }

    /**
     * @return string
     */
    private function getIndexerId()
    {
        return $this->data['indexer_id'];
    }

    /**
     * @param Dimension[] $dimensions
     * @return int
     */
    private function resolveScopeId($dimensions)
    {
        $dimension = current($dimensions);
        return $this->scopeResolver->getScope($dimension->getValue())->getId();
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }
}
