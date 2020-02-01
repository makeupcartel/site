<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Search;

use Amasty\ElasticSearch\Api\RelevanceRuleRepositoryInterface;
use Amasty\ElasticSearch\Model\Client\ClientRepositoryInterface;
use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\RequestInterface;
use Amasty\ElasticSearch\Model\Search\GetResponse\GetAggregations;

class Adapter implements AdapterInterface
{
    const REQUEST_QUERY = 'amasty_elastic_query';
    const HITS = 'hits';
    const PRODUCTS = 'products';

    /**
     * @var GetRequestQuery
     */
    private $getRequestQuery;

    /**
     * @var GetResponse
     */
    private $getElasticResponse;

    /**
     * @var GetAggregations
     */
    private $getAggregations;

    /**
     * @var ClientRepositoryInterface
     */
    private $clientRepository;

    /**
     * @var RelevanceRuleRepositoryInterface
     */
    private $relevanceRuleRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(
        ClientRepositoryInterface $clientRepository,
        GetAggregations $getAggregations,
        GetRequestQuery $getRequestQuery,
        GetResponse $getElasticResponse,
        RelevanceRuleRepositoryInterface $relevanceRuleRepository,
        \Magento\Framework\Registry $registry,
        \Amasty\ElasticSearch\Model\Search\Logger $logger
    ) {
        $this->getAggregations = $getAggregations;
        $this->getRequestQuery = $getRequestQuery;
        $this->getElasticResponse = $getElasticResponse;
        $this->clientRepository = $clientRepository;
        $this->relevanceRuleRepository = $relevanceRuleRepository;
        $this->registry = $registry;
        $this->logger = $logger;
    }

    /**
     * @param RequestInterface $request
     * @return \Magento\Framework\Search\Response\QueryResponse|mixed
     * @throws \Elasticsearch\Common\Exceptions\Missing404Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function query(RequestInterface $request)
    {
        $client = $this->clientRepository->get();
        $requestQuery = $this->getRequestQuery->execute($request);
        $elasticResponse = $client->search($requestQuery);
        $elasticDocuments = isset($elasticResponse['hits']['hits']) ? $elasticResponse['hits']['hits'] : [];
        $this->registry->unregister(self::REQUEST_QUERY);
        $this->registry->register(self::REQUEST_QUERY, $requestQuery['body']['query']);
        $aggregations = $this->getAggregations->execute($request, $elasticResponse);
        $this->registry->unregister(self::REQUEST_QUERY);
        $responseQuery = $this->getElasticResponse->execute($elasticDocuments, $aggregations);
        if (in_array($request->getName(), ['quick_search_container', 'catalogsearch_fulltext'], true)) {
            $productIds = array_map(function ($item) {
                return (int)$item['_id'];
            }, $elasticResponse['hits']['hits']);
            $responseQuery = $this->applyRelevanceRules($responseQuery, $productIds);
        }
        $this->logger->log($request, $responseQuery, $requestQuery, $elasticResponse);
        return $responseQuery;
    }

    /**
     * @param RequestInterface $request
     * @return array
     * @throws \Elasticsearch\Common\Exceptions\Missing404Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function queryAdvancedSearchProduct(RequestInterface $request)
    {
        $client = $this->clientRepository->get();
        $requestQuery = $this->getRequestQuery->execute($request);
        unset($requestQuery['aggregations']);
        $requestQuery['body']['_source'] = ['amasty_xsearch_fulltext'];
        $elasticResponse = $client->search($requestQuery);
        $products = [];
        if (!empty($elasticResponse['hits']['hits'])) {
            foreach ($elasticResponse['hits']['hits'] as $index => $product) {
                if (!empty($product['_source']['amasty_xsearch_fulltext'])) {
                    $products[$product['_id']] = $product['_source']['amasty_xsearch_fulltext'];
                }
            }
        }

        $hits = $elasticResponse['hits']['total'];
        return [self::HITS => $hits, self::PRODUCTS => $products];
    }

    /**
     * @param \Magento\Framework\Search\Response\QueryResponse $responseQuery
     * @param int[] $productIds
     * @return \Magento\Framework\Search\Response\QueryResponse
     */
    private function applyRelevanceRules(\Magento\Framework\Search\Response\QueryResponse $responseQuery, $productIds)
    {
        if ($responseQuery->count()) {
            $boostMultipliers = $this->relevanceRuleRepository->getProductBoostMultipliers($productIds);
            foreach ($responseQuery->getIterator() as $document) {
                if (isset($boostMultipliers[$document->getId()])) {
                    $score = $boostMultipliers[$document->getId()] * $document->getCustomAttribute('score')->getValue();
                    $document->getCustomAttribute('score')->setValue($score);
                }
            }
        }

        return $responseQuery;
    }
}
