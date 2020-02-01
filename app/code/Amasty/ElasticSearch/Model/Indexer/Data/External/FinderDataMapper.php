<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Indexer\Data\External;

use Amasty\ElasticSearch\Api\Data\Indexer\Data\DataMapperInterface;
use Amasty\ElasticSearch\Model\Indexer\Structure\EntityBuilder\External\Finder;
use Magento\Catalog\Api\Data\ProductAttributeInterface;

class FinderDataMapper implements DataMapperInterface
{
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    private $eavAttribute;

    /**
     * @var Finder
     */
    private $externalBuilder;

    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        Finder $externalBuilder
    ) {
        $this->eavAttribute = $eavAttribute;
        $this->externalBuilder = $externalBuilder;
    }

    /**
     * @param array $documentData
     * @param int $storeId
     * @param array $context
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function map(array $documentData, $storeId, array $context = [])
    {
        $documents = [];
        $externalAttributeIds = [];
        foreach (array_keys($this->externalBuilder->getExtraValueFields()) as $attributeCode) {
            $externalAttributeIds[$attributeCode . '_value']
                = $this->eavAttribute->getIdByCode(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode);
        }

        foreach ($documentData as $productId => $indexData) {
            foreach ($externalAttributeIds as $filterName => $attributeId) {
                if (isset($indexData[$attributeId])) {
                    if (is_array($indexData[$attributeId])) {
                        $value = isset($indexData[$attributeId][$productId])
                            ? $indexData[$attributeId][$productId] : '';
                    } else {
                        $value = $indexData[$attributeId];
                    }

                    $documents[$productId][$filterName] = $value;
                }
            }
        }

        return $documents;
    }
}
