<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Model\System\Config\Backend;

class Weight extends \Magento\Framework\App\Config\Value
{

    /**
     * @var \Magento\Framework\Math\Random
     */
    private $mathRandom;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var \Amasty\Xsearch\Helper\Data
     */
    private $xSearchHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Attribute
     */
    private $attributeResource;

    /**
     * Weight constructor.
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository
     * @param \Magento\Framework\Model\Context $context
     * @param \Amasty\Xsearch\Helper\Data $xSearchHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        \Amasty\Xsearch\Helper\Data $xSearchHelper,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Catalog\Model\ResourceModel\Attribute $attributeResource,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->mathRandom = $mathRandom;
        $this->attributeRepository = $attributeRepository;
        $this->xSearchHelper = $xSearchHelper;
        $this->attributeResource = $attributeResource;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Prepare data before save
     *
     * @return $this
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if (!$value) {
            return $this;
        }

        $result = [];
        foreach ($value as $data) {
            if (!$data
                || !is_array($data)
                || !(isset($data['weight']) && isset($data['attributes_weight']))
            ) {
                continue;
            }

            $result[$data['attributes_weight']] = $data['weight'];
            $this->setWeightAndSearchable($data['attributes_weight'], $data['weight']);
        }

        $this->deactivateSearchable($value);
        $this->setValue(serialize($result));

        return $this;
    }

    /**
     * @return $this
     */
    protected function _afterLoad()
    {
        $value = $this->encodeArrayFieldValue($this->getActiveInSearchAttributes());
        $this->setValue($value);

        return $this;
    }

    /**
     * @param array $value
     * @return array
     */
    private function encodeArrayFieldValue(array $value)
    {
        $result = [];
        foreach ($value as $attributes => $weight) {
            $resultId = $this->mathRandom->getUniqueHash('_');
            $result[$resultId] = ['attributes' => $attributes, 'weight' => $weight];
        }

        return $result;
    }

    /**
     * Get attributes in wich is_searchable true
     * @return array
     */
    private function getActiveInSearchAttributes()
    {
        $values = [];

        $productAttributes = $this->xSearchHelper->getProductAttributes();
        if (!$productAttributes) {
            return $values;
        }

        foreach ($productAttributes as $attribute) {
            if ($attribute->getIsSearchable()) {
                $values[$attribute->getAttributeCode()] = $attribute->getSearchWeight();
            }
        }

        return $values;
    }

    /**
     * @param $attributeCode
     * @param $weight
     */
    private function setWeightAndSearchable($attributeCode, $weight)
    {
        $attribute = $this->attributeRepository->get($attributeCode);
        $attribute->setSearchWeight($weight);
        $attribute->setIsSearchable(true);

        /* saving with resource model, because magento repository on version less 2.1.8 break attribute options*/
        $this->attributeResource->save($attribute);
    }

    /**
     * Set in the attribute is_searchable in false
     * @param $values
     */
    private function deactivateSearchable($values)
    {
        if (!$values) {
            return;
        }

        $productAttributes = $this->xSearchHelper->getProductAttributes('is_searchable');
        if (!$productAttributes) {
            return;
        }

        $productAttributes = array_flip($productAttributes);
        foreach ($values as $value) {
            if ($value) {
                unset($productAttributes[$value['attributes_weight']]);
            }
        }

        foreach ($productAttributes as $attribute => $value) {
            $attribute = $this->attributeRepository->get($attribute);
            $attribute->setIsSearchable(false);
            $this->attributeResource->save($attribute);
        }
    }
}
