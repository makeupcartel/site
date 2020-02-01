<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Search\GetResponse;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\Dynamic\Algorithm\Repository;
use Magento\Framework\Search\Dynamic\EntityStorage;
use Magento\Framework\Search\Dynamic\EntityStorageFactory;
use Amasty\ElasticSearch\Model\Search\DataProvider;
use Magento\Framework\Search\Request\Dimension;

class GetAggregations
{
    /**
     * @var Repository
     */
    private $dynamicAlgorithmRepository;

    /**
     * @var EntityStorageFactory
     */
    private $dynamicEntityStorageFactory;

    /**
     * @var DataProvider
     */
    private $dataProvider;

    public function __construct(
        Repository $dynamicAlgorithmRepository,
        EntityStorageFactory $dynamicEntityStorageFactory,
        DataProvider $dataProvider
    ) {
        $this->dynamicAlgorithmRepository = $dynamicAlgorithmRepository;
        $this->dynamicEntityStorageFactory = $dynamicEntityStorageFactory;
        $this->dataProvider = $dataProvider;
    }

    /**
     * @param \Magento\Framework\Search\RequestInterface $request
     * @param array $elasticResponse
     * @return array
     * @throws LocalizedException
     */
    public function execute(\Magento\Framework\Search\RequestInterface $request, array $elasticResponse)
    {
        $aggregations = [];
        $buckets = $request->getAggregation();

        foreach ($buckets as $bucket) {
            switch ($bucket->getType()) {
                case BucketInterface::TYPE_DYNAMIC:
                    $aggregations[$bucket->getName()] = $this
                        ->getDynamicBucket($bucket, $request->getDimensions(), $elasticResponse);
                    break;
                case BucketInterface::TYPE_TERM:
                    $aggregations[$bucket->getName()] = $this->getTermBucket($bucket, $elasticResponse);
                    break;
                default:
                    throw new LocalizedException(__("Incorrect Bucket type: %1.", $bucket->getType()));
            }
        }

        return $aggregations;
    }

    /**
     * @param BucketInterface $bucket
     * @param array $elasticResponse
     * @return array
     */
    public function getTermBucket(BucketInterface $bucket, array $elasticResponse)
    {
        $values = [];
        foreach ($elasticResponse['aggregations'][$bucket->getName()]['buckets'] as $resultBucket) {
            $values[$resultBucket['key']] = [
                'value' => $resultBucket['key'],
                'count' => $resultBucket['doc_count'],
            ];
        }

        return $values;
    }

    /**
     * @param BucketInterface $bucket
     * @param Dimension[] $dimensions
     * @param array $elasticResponse
     * @return array
     * @throws LocalizedException
     */
    public function getDynamicBucket(BucketInterface $bucket, array $dimensions, array $elasticResponse)
    {
        $algorithm = $this->dynamicAlgorithmRepository->get(
            $bucket->getMethod(),
            ['dataProvider' => $this->dataProvider]
        );
        $data = $algorithm->getItems(
            $bucket,
            $dimensions,
            $this->convertQuerytoEntity($elasticResponse)
        );
        return $this->processFromToData($data);
    }

    /**
     * @param array $data
     * @return array
     */
    private function processFromToData($data)
    {
        $result = [];
        foreach ($data as $value) {
            $from = is_numeric($value['from']) ? $value['from'] : '*';
            $to = is_numeric($value['to']) ? $value['to'] : '*';
            unset($value['from'], $value['to']);
            $fromToValue = "{$from}_{$to}";

            $result[$fromToValue] = array_merge(['value' => $fromToValue], $value);
        }

        return $result;
    }

    /**
     * @param array $queryResult
     * @return EntityStorage
     */
    private function convertQuerytoEntity(array $queryResult)
    {
        $documentIds = [];
        if (isset($queryResult['hits']['hits']) && !empty($queryResult['hits']['hits'])) {
            foreach ($queryResult['hits']['hits'] as $document) {
                $documentIds[] = $document['_id'];
            }
        }

        return $this->dynamicEntityStorageFactory->create($documentIds);
    }
}
