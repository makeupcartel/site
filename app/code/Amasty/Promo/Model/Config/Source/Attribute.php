<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */

namespace Amasty\Promo\Model\Config\Source;

/**
 * Class Attribute
 */
class Attribute implements \Magento\Framework\Option\ArrayInterface
{
    /** @var \Magento\Framework\Convert\DataObject */
    protected $convertDataObject;

    /** @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory */
    protected $attributeCollectionFactory;

    /**
     * @param \Magento\Framework\Convert\DataObject                                    $convertDataObject
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory
     */
    public function __construct(
        \Magento\Framework\Convert\DataObject $convertDataObject,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory
    ) {
        $this->convertDataObject          = $convertDataObject;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        $arr         = $this->toArray();
        foreach ($arr as $value => $label) {
            $optionArray[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $collection = $this->attributeCollectionFactory->create();
        $collection->addFieldToSelect(['attribute_code', 'attribute_id', 'frontend_label']);
        $collection->setFrontendInputTypeFilter(['in' => ['text', 'textarea']]);

        return $this->convertDataObject->toOptionHash($collection->getItems(), 'attribute_code', 'frontend_label');
    }
}
