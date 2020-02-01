<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Search;

use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\Api\AttributeInterface;

class GetResponse
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @param array $elasticDocuments
     * @param array $rawAggregations
     * @return \Magento\Framework\Search\Response\QueryResponse|mixed
     */
    public function execute(array $elasticDocuments, array $rawAggregations)
    {
        $documents = $this->getDocuments($elasticDocuments);
        $aggregations = $this->getAggregations($rawAggregations);

        return $this->objectManager->create(
            \Magento\Framework\Search\Response\QueryResponse::class,
            ['documents' => $documents, 'aggregations' => $aggregations, 'total' => count($documents)]
        );
    }

    /**
     * @param array[] $elasticDocuments
     * @return \Magento\Framework\Api\Search\Document[]
     */
    private function getDocuments(array $elasticDocuments)
    {
        $documents = [];
        foreach ($elasticDocuments as $rawDocument) {
            $attributes = [];
            $documentId = null;
            foreach ($rawDocument as $fieldName => $value) {
                if ($fieldName === '_id') {
                    $documentId = $value;
                } elseif ($fieldName === '_score') {
                    $attributes['score'] = $this->objectManager->create(
                        \Magento\Framework\Api\AttributeValue::class,
                        [
                            'data' => [
                                AttributeInterface::ATTRIBUTE_CODE => $fieldName,
                                AttributeInterface::VALUE => $value
                            ]
                        ]
                    );
                }
            }

            $documents[] = $this->objectManager->create(
                \Magento\Framework\Api\Search\Document::class,
                [
                    'data' => [
                        DocumentInterface::ID => $documentId,
                        CustomAttributesDataInterface::CUSTOM_ATTRIBUTES => $attributes
                    ]
                ]
            );
        }

        return $documents;
    }

    /**
     * @param array[] $rawAggregations
     * @return \Magento\Framework\Search\Response\Aggregation
     */
    private function getAggregations(array $rawAggregations)
    {
        $buckets = [];
        foreach ($rawAggregations as $bucketName => $aggregation) {
            $values = [];
            foreach ($aggregation as $aggregationName => $metrics) {
                $values[] = $this->objectManager->create(
                    \Magento\Framework\Search\Response\Aggregation\Value::class,
                    [
                        'value' => $aggregationName,
                        'metrics' => $metrics,
                    ]
                );
            }

            $buckets[$bucketName] = $this->objectManager->create(
                \Magento\Framework\Search\Response\Bucket::class,
                [
                    'name' => $bucketName,
                    'values' => $values
                ]
            );
        }

        return $this->objectManager->create(
            \Magento\Framework\Search\Response\Aggregation::class,
            ['buckets' => $buckets]
        );
    }
}
