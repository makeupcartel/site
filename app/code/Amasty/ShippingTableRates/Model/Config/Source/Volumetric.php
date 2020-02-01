<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;

class Volumetric implements ArrayInterface
{
    const PRODUCT_ATTRIBUTE_TYPE_ID = 4;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Convert\DataObject
     */
    private $objectConverter;

    public function __construct(
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Convert\DataObject $objectConverter
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->objectConverter = $objectConverter;
    }

    /**
     * The method preapres list of product attributes
     *
     * @return array
     */
    public function toOptionArray()
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            'main_table.'.AttributeSet::KEY_ENTITY_TYPE_ID,
            self::PRODUCT_ATTRIBUTE_TYPE_ID
        )->create();

        $configProductAttributeList = ['value' => 0, 'label' => __('None')];
        $productAttributes = $this->attributeRepository->getList($searchCriteria)->getItems();
        $options = $this->objectConverter->toOptionArray($productAttributes, 'attribute_code', 'frontend_label');
        array_unshift($options, $configProductAttributeList);

        return $options;
    }
}
