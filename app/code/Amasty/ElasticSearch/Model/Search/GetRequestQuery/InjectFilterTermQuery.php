<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Search\GetRequestQuery;

use Magento\Framework\Search\Request\QueryInterface;
use Amasty\ElasticSearch\Model\Search\GetRequestQuery\GetAggregations\FieldMapper;

class InjectFilterTermQuery implements InjectSubqueryInterface
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
        /** @var \Magento\Framework\Search\Request\Filter\Term $filter */
        $filter = $request->getReference();
        $fieldName = $this->fieldMapper->mapFieldName($filter->getField());
        if (!isset($elasticQuery['bool'][$conditionType])) {
            $elasticQuery['bool'][$conditionType] = [];
        }

        $filterValue = $filter->getValue();
        $filterType = is_array($filterValue) ? 'terms' : 'term';
        if(is_array($filterValue) && array_key_exists('nin', $filterValue)) {
            $filterValue = $filterValue['nin'];
        }

        $elasticQuery['bool'][$conditionType][] = [
            $filterType => [
                $fieldName => $filterValue
            ]
        ];

        return $elasticQuery;
    }
}
