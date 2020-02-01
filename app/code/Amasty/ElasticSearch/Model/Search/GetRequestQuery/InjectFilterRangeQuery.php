<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Search\GetRequestQuery;

use Magento\Framework\Search\Request\QueryInterface;
use Amasty\ElasticSearch\Model\Search\GetRequestQuery\GetAggregations\FieldMapper;

class InjectFilterRangeQuery implements InjectSubqueryInterface
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
     * @inheritdoc
     */
    public function execute(array $elasticQuery, QueryInterface $request, $conditionType)
    {
        /** @var \Magento\Framework\Search\Request\Query\Filter $request */
        /** @var \Magento\Framework\Search\Request\Filter\Range $filter */
        $filter = $request->getReference();
        $fieldName = $this->fieldMapper->mapFieldName($filter->getField());
        $filterQuery = [];
        if ($filter->getFrom()) {
            $filterQuery['range'][$fieldName]['gte'] = $filter->getFrom();
        }

        if ($filter->getTo()) {
            $filterQuery['range'][$fieldName]['lte'] = $filter->getTo();
        }

        if (!isset($elasticQuery['bool'][$conditionType])) {
            $elasticQuery['bool'][$conditionType] = [];
        }

        $elasticQuery['bool'][$conditionType][] = $filterQuery;
        return $elasticQuery;
    }
}
