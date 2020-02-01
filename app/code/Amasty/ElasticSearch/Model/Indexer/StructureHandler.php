<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Indexer;

use Magento\Framework\Indexer\IndexStructureInterface;
use Amasty\ElasticSearch\Model\Client\Elasticsearch as ElasticsearchClient;
use Magento\Framework\App\ScopeResolverInterface;

class StructureHandler implements IndexStructureInterface
{
    /**
     * @var ElasticsearchClient
     */
    private $client;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    public function __construct(
        ElasticsearchClient $client,
        ScopeResolverInterface $scopeResolver
    ) {
        $this->client = $client;
        $this->scopeResolver = $scopeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        $indexerId,
        array $dimensions = []
    ) {
        $dimension = current($dimensions);
        $scopeId = $this->scopeResolver->getScope($dimension->getValue())->getId();
        $this->client->cleanIndex($scopeId, $indexerId);
    }

    /**
     * {@inheritdoc}
     *
     */
    public function create(
        $indexerId,
        array $fields,
        array $dimensions = []
    ) {
        $dimension = current($dimensions);
        $scopeId = $this->scopeResolver->getScope($dimension->getValue())->getId();
        $this->client->checkIndex($scopeId, $indexerId);
    }
}
