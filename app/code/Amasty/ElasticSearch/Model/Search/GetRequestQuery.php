<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Search;

use Amasty\ElasticSearch\Model\Client\ClientRepository;
use Amasty\ElasticSearch\Model\Client\Elasticsearch;
use Amasty\ElasticSearch\Model\Search\GetRequestQuery\InjectSubqueryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Search\Request\QueryInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Request\Query\BoolExpression as BoolQuery;
use Magento\Framework\Search\Request\Query\Filter as FilterQuery;
use Magento\Framework\Search\Request\Query\Match as MatchQuery;

class GetRequestQuery
{
    const QUERY_CONDITION_MUST_NOT = 'must_not';

    /**
     * @var \Amasty\ElasticSearch\Model\Config
     */
    private $config;

    /**
     * @var GetRequestQuery\GetAggregations
     */
    private $getAggregations;

    /**
     * @var InjectSubqueryInterface[]
     */
    private $subqueryInjectors;

    /**
     * @var \Amasty\ElasticSearch\Model\Client\ClientRepositoryInterface
     */
    private $clientRepository;
    /**
     * @var string[]
     */
    private $booleanMethods = [
        BoolQuery::QUERY_CONDITION_MUST => 'getMust',
        BoolQuery::QUERY_CONDITION_SHOULD => 'getShould',
        self::QUERY_CONDITION_MUST_NOT => 'getMustNot'
    ];

    public function __construct(
        \Amasty\ElasticSearch\Model\Config $config,
        ClientRepository $clientRepository,
        GetRequestQuery\GetAggregations $getAggregations,
        array $subqueryInjectors
    ) {
        $this->config = $config;
        $this->clientRepository = $clientRepository;
        $this->subqueryInjectors = $subqueryInjectors;
        $this->getAggregations = $getAggregations;
    }

    /**
     * @param RequestInterface $request
     * @return array
     * @throws LocalizedException
     */
    public function execute(RequestInterface $request)
    {
        $storeId = current($request->getDimensions())->getValue();
        $size = $this->config->getModuleConfig('catalog/max_items') ?: $request->getSize();
        $query = [
            'index' => $this->clientRepository->get()->getIndexName('product', $storeId),
            'type'  => $this->config->getEntityType(),
            'body'  => [
                'from'          => $request->getFrom(),
                'size'          => $size,
                'stored_fields' => ['_id', '_score'],
                'query' => $this->processQuery($request->getQuery(), [], BoolQuery::QUERY_CONDITION_MUST)
            ],
        ];

        $aggregations = $this->getAggregations->execute($request);
        if ($aggregations) {
            $query['body']['aggregations'] = $aggregations;
        }

        if (isset($query['body']['query']['bool']['should'])) {
            $query['body']['query']['bool']['minimum_should_match'] = 1;
        }

        return $query;
    }

    /**
     * @param string $term
     * @param int $storeId
     * @param string $fulltextField
     * @param string $entityType
     * @return array
     */
    public function executeExternalByFulltext($term, $storeId, $fulltextField, $entityType)
    {
        $filterQuery = ['must' => [['query_string' => [
            'default_field' => $fulltextField,
            'query' => $term
        ]]]];
        $index = Elasticsearch::EXTERNAL_INDEX . '_' . $entityType;
        $query = [
            'index' => $this->clientRepository->get()->getIndexName($index, $storeId),
            'type'  => $this->config->getEntityType(),
            'body'  => [
                'from'      => 0,
                'size'      => 10000,
                '_source'   => [],
                'query'     => ['bool' => $filterQuery]
            ],
        ];

        return $query;
    }

    /**
     * @param QueryInterface $request
     * @param array $elasticQuery
     * @param $conditionType
     * @return array
     * @throws LocalizedException
     */
    private function processQuery(QueryInterface $request, array $elasticQuery, $conditionType)
    {
        switch ($request->getType()) {
            case QueryInterface::TYPE_BOOL:
                /** @var BoolQuery $request */
                foreach ($this->booleanMethods as $conditionType => $method) {
                    foreach ($request->{$method}() as $subQuery) {
                        $elasticQuery = $this->processQuery($subQuery, $elasticQuery, $conditionType);
                    }
                }

                break;
            case QueryInterface::TYPE_MATCH:
                /** @var MatchQuery $request */
                $addSubquery = $this->getSubqueryInjectorByType('match');
                $elasticQuery = $addSubquery->execute($elasticQuery, $request, $conditionType);
                break;
            case QueryInterface::TYPE_FILTER:
                /** @var FilterQuery $request */
                if ($request->getReferenceType() === FilterQuery::REFERENCE_QUERY) {
                    $elasticQuery = $this->processQuery($request->getReference(), $elasticQuery, $conditionType);
                } elseif ($request->getReferenceType() === FilterQuery::REFERENCE_FILTER) {
                    $addSubquery = $this->getSubqueryInjectorByType($request->getReference()->getType());
                    $elasticQuery = $addSubquery->execute($elasticQuery, $request, $conditionType);
                } else {
                    throw new LocalizedException(
                        __("Builder for %1 reference type doesn't exist", $request->getReferenceType())
                    );
                }

                break;
            default:
                throw new LocalizedException(__("Processor for %1 type doesn't exist", $request->getType()));
        }

        return $elasticQuery;
    }

    /**
     * @param string $filterType
     * @return InjectSubqueryInterface
     * @throws LocalizedException
     */
    private function getSubqueryInjectorByType($filterType)
    {
        if (!array_key_exists($filterType, $this->subqueryInjectors)) {
            throw new LocalizedException(__("'%1' filter type is not supported", $filterType));
        }

        return $this->subqueryInjectors[$filterType];
    }
}
