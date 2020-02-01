<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Search\GetRequestQuery;

use Amasty\ElasticSearch\Model\Config\QuerySettings;
use Amasty\ElasticSearch\Model\GetNonTextAttributes;
use Amasty\ElasticSearch\Model\Search\GetRequestQuery\ServicePreprocessor\Synonyms;
use Amasty\ElasticSearch\Model\Source\WildcardMode;
use Magento\Framework\Search\Request\QueryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;

class InjectMatchQuery implements InjectSubqueryInterface
{
    /**
     * @var \Amasty\ElasticSearch\Model\Config
     */
    private $config;

    /**
     * @var GetNonTextAttributes
     */
    private $getNonTextAttributes;

    /**
     * @var ServicePreprocessor\PreprocessorInterface[]
     */
    private $services;

    /**
     * @var AttributeCollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @var array
     */
    private $selectAttributes = [];

    /**
     * @var array
     */
    private $excludedAttributes = [];

    public function __construct(
        \Amasty\ElasticSearch\Model\Config $config,
        GetNonTextAttributes $getNonTextAttributes,
        AttributeCollectionFactory $attributeCollectionFactory,
        array $services = []
    ) {
        $this->services = $services;
        $this->getNonTextAttributes = $getNonTextAttributes;
        $this->config = $config;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->_construct();
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $attributeCollection = $this->attributeCollectionFactory->create();
        $attributeCollection
            ->getSelect()
            ->setPart('columns', [])
            ->columns(['frontend_input', 'attribute_code']);
        foreach ($attributeCollection as $attribute) {
            if (in_array($attribute->getFrontendInput(), ['select', 'multiselect'], true)) {
                $this->selectAttributes[] = $attribute->getAttributeCode();
            } elseif ($attribute->getFrontendInput() === 'boolean') {
                $this->excludedAttributes[] = $attribute->getAttributeCode();
            }
        }

        $this->excludedAttributes = array_merge($this->excludedAttributes, $this->getNonTextAttributes->execute());
    }

    /**
     * @inheritdoc
     */
    public function execute(array $elasticQuery, QueryInterface $request, $conditionType)
    {
        $requestValue = $this->processServices($request->getValue());
        $requestValue = ['condition' => $conditionType, 'value' => $requestValue];
        $conditionQuery = $this->getConditionsByMatches($request, $requestValue);
        foreach ($conditionQuery as $subCondition) {
            $elasticQuery['bool'][$subCondition['condition']][]= $subCondition['body'];
        }

        return $elasticQuery;
    }

    /**
     * @param string $requestValue
     * @return string
     */
    private function processServices($requestValue)
    {
        $requestValue = strip_tags($requestValue);
        $pattern = '/(\+|-|\/|&&|\|\||!|\(|\)|\{|}|\[|]|\^|"|~|\*|\?|:|\\\)/';
        $requestValue = preg_replace($pattern, '\\\$1', $requestValue);
        foreach ($this->services as $service) {
            $requestValue = $service->process($requestValue);
        }

        return $requestValue;
    }

    /**
     * @param QueryInterface $request
     * @param array $requestValue
     * @return array
     */
    private function getConditionsByMatches(QueryInterface $request, $requestValue)
    {
        $conditions = [];
        foreach ($request->getMatches() as $match) {
            if (in_array($match['field'], $this->excludedAttributes, true)) {
                continue;
            }

            $field = $this->getFieldName($match['field']);
            $value = $this->getValue($match['field'], $requestValue['value']);
            $conditions[]  = [
                'body' => [
                    'query_string' => [
                        'default_field' => $field,
                        'query' => $value,
                        'boost' => pow(2, isset($match['boost']) ? $match['boost'] : 1),
                    ],
                ],
                'condition' => $requestValue['condition']
            ];
        }

        return $conditions;
    }

    /**
     * @param string $name
     * @return string
     */
    private function getFieldName($name)
    {
        if (in_array($name, $this->selectAttributes, true)) {
            $name .= '_value';
        }

        return $name;
    }

    /**
     * @param string $name
     * @param string $value
     * @return array
     */
    private function getValue($name, $value)
    {
        $queryConfig = $this->config->getQuerySettingByAttributeCode($name);
        $wildcardType = $this->config->getModuleConfig('catalog/wildcard_mode');
        $wildMinChars = $this->config->getModuleConfig('catalog/wildcard_symbols');
        $wildcard = $queryConfig[QuerySettings::WILDCARD];
        $spellMinChars = $this->config->getModuleConfig('catalog/spellcorrection_symbols');
        $spellCorrection = $queryConfig[QuerySettings::SPELLING];
        $combination = $queryConfig[QuerySettings::COMBINING] ? ' AND ' : ' OR ';

        $value = array_filter(explode(' ', $value));
        foreach ($value as &$term) {
            if ($wildcard && (strlen($term) >= $wildMinChars)) {
                switch ($wildcardType) {
                    case WildcardMode::BOTH:
                        $term = '*' . $term . '*';
                        break;
                    case WildcardMode::PREFIX:
                        $term = '*' . $term;
                        break;
                    case WildcardMode::SUFFIX:
                        $term .= '*';
                        break;
                }
            } elseif ($spellCorrection && (strlen($term) >= $spellMinChars)) {
                $term .= '~1';
            }
        }

        return implode($combination, $value);
    }
}
