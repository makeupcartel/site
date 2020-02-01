<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Search\GetRequestQuery;

use Magento\Framework\Search\Request\BucketInterface;
use Amasty\ElasticSearch\Model\Search\GetRequestQuery\GetAggregations\FieldMapper;

class GetAggregations
{
    /**
     * @var FieldMapper
     */
    private $fieldMapper;

    public function __construct(FieldMapper $fieldMapper)
    {
        $this->fieldMapper = $fieldMapper;
    }

    /**
     * @param \Magento\Framework\Search\RequestInterface $request
     * @return array
     */
    public function execute(\Magento\Framework\Search\RequestInterface $request)
    {
        $aggregationsQuery = [];
        foreach ($request->getAggregation() as $bucket) {
            $aggregationsQuery = array_merge_recursive($aggregationsQuery, $this->buildBucket($bucket));
        }

        return $aggregationsQuery;
    }

    /**
     * @param BucketInterface $bucket
     * @return array
     */
    private function buildBucket(BucketInterface $bucket)
    {

        if ($bucket->getType() == BucketInterface::TYPE_TERM) {
            return [
                $bucket->getName() => [
                    'terms' => [
                        'field' => $this->fieldMapper->mapFieldName($bucket->getField()),
                        'size'  => 1000,
                    ],
                ],
            ];
        } elseif ($bucket->getType() == BucketInterface::TYPE_DYNAMIC) {
            return [
                $bucket->getName() => [
                    'extended_stats' => [
                        'field' => $this->fieldMapper->mapFieldName($bucket->getField()),
                    ],
                ],
            ];
        }

        return [];
    }
}
