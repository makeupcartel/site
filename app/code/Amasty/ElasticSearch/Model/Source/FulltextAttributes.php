<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Source;

use Amasty\ElasticSearch\Model\GetNonTextAttributes;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection as AttributeCollection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Amasty\ElasticSearch\Model\Indexer\Structure\EntityBuilder\Product as ProductBuilder;

class FulltextAttributes implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var GetNonTextAttributes
     */
    private $getNonTextAttributes;

    /**
     * @var AttributeCollection
     */
    private $attributeCollection;

    /**
     * @var ProductBuilder
     */
    private $productBuilder;

    /**
     * @var array
     */
    private $fulltextAttributes;

    public function __construct(
        GetNonTextAttributes $getNonTextAttributes,
        AttributeCollectionFactory $attributeCollectionFactory,
        ProductBuilder $productBuilder
    ) {
        $this->getNonTextAttributes = $getNonTextAttributes;
        $this->attributeCollection = $attributeCollectionFactory->create();
        $this->productBuilder = $productBuilder;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $attributes = [];
        foreach ($this->toArray() as $code => $label) {
            $attributes[] = ['value' => $code, 'label' => $label];
        }

        return $attributes;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        if ($this->fulltextAttributes === null) {
            $this->fulltextAttributes = [];
            /** @var \Magento\Eav\Model\Attribute $attribute */
            foreach ($this->attributeCollection as $attribute) {
                if ($attribute->getIsSearchable()
                    && !in_array($attribute->getAttributeCode(), $this->getNonTextAttributes->execute(), true)
                ) {
                    $this->fulltextAttributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
                }
            }
        }

        return $this->fulltextAttributes;
    }
}
