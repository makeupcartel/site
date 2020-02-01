<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Model\Rule\Condition;

use Magento\Rule\Model\Condition as Condition;

/**
 * @method string getAttribute() customer attribute code
 */
class Customer extends \Magento\Rule\Model\Condition\AbstractCondition
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer
     */
    private $resource;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $yesnoOptions;

    public function __construct(
        Condition\Context $context,
        \Magento\Customer\Model\ResourceModel\Customer $resource,
        \Magento\Config\Model\Config\Source\Yesno $yesnoOptions,
        array $data = []
    ) {
        $this->resource = $resource;
        parent::__construct($context, $data);
        $this->yesnoOptions = $yesnoOptions;
    }

    /**
     * Retrieve attribute object
     *
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    public function getAttributeObject()
    {
        return $this->resource->getAttribute($this->getAttribute());
    }

    /**
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $customerAttributes = $this->resource
            ->loadAllAttributes()
            ->getAttributesByCode();

        $attributes = [];
        /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
        foreach ($customerAttributes as $attribute) {
            if (!($attribute->getFrontendLabel()) || !($attribute->getAttributeCode())) {
                continue;
            }

            $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }
        $this->_addSpecialAttributes($attributes);
        asort($attributes);
        $this->setAttributeOption($attributes);
        return $this;
    }

    /**
     * Add special attributes
     *
     * @param array &$attributes
     * @return void
     */
    protected function _addSpecialAttributes(array &$attributes)
    {
        $attributes['entity_id'] = __('Customer ID');
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    /**
     * This value will define which operators will be available for this condition.
     *
     * Possible values are: string, numeric, date, select, multiselect, grid, boolean
     *
     * @return string
     */
    public function getInputType()
    {
        if ($this->getAttribute() === 'entity_id') {
            return 'grid';
        }
        $customerAttribute = $this->getAttributeObject();
        if (!$customerAttribute) {
            return parent::getInputType();
        }

        return $this->getInputTypeFromAttribute($customerAttribute);
    }

    /**
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $customerAttribute
     *
     * @return string
     */
    protected function getInputTypeFromAttribute($customerAttribute)
    {
        if (!is_object($customerAttribute)) {
            $customerAttribute = $this->getAttributeObject();
        }
        $possibleTypes = ['string', 'numeric', 'date', 'select', 'multiselect', 'grid', 'boolean'];
        if (in_array($customerAttribute->getFrontendInput(), $possibleTypes)) {
            return $customerAttribute->getFrontendInput();
        }
        switch ($customerAttribute->getFrontendInput()) {
            case 'gallery':
            case 'media_image':
            case 'selectimg': // amasty customer attribute
                return 'select';
            case 'multiselectimg': // amasty customer attribute
                return 'multiselect';
        }

        return 'string';
    }

    /**
     * {@inheritdoc}
     */
    public function getValueElement()
    {
        $element = parent::getValueElement();
        switch ($this->getInputType()) {
            case 'date':
                $element->setClass('hasDatepicker');
                break;
        }
        return $element;
    }

    /**
     * Value element type will define renderer for condition value element
     *
     * @see \Magento\Framework\Data\Form\Element
     * @return string
     */
    public function getValueElementType()
    {
        $customerAttribute = $this->getAttributeObject();

        if ($this->getAttribute() === 'entity_id') {
            return 'text';
        }
        if (!is_object($customerAttribute)) {
            return parent::getValueElementType();
        }

        $availableTypes = [
            'checkbox',
            'checkboxes',
            'date',
            'editablemultiselect',
            'editor',
            'fieldset',
            'file',
            'gallery',
            'image',
            'imagefile',
            'multiline',
            'multiselect',
            'radio',
            'radios',
            'select',
            'text',
            'textarea',
            'time'
        ];

        if (in_array($customerAttribute->getFrontendInput(), $availableTypes)) {
            return $customerAttribute->getFrontendInput();
        }
        switch ($customerAttribute->getFrontendInput()) {
            case 'selectimg':
            case 'boolean':
                return 'select';
            case 'multiselectimg':
                return 'multiselect';
        }

        return parent::getValueElementType();
    }

    /**
     * @return array
     */
    public function getValueSelectOptions()
    {
        $selectOptions = [];
        $attributeObject = $this->getAttributeObject();

        if (is_object($attributeObject) && $attributeObject->usesSource()) {
            $addEmptyOption = true;
            if ($attributeObject->getFrontendInput() == 'multiselect') {
                $addEmptyOption = false;
            }
            $selectOptions = $attributeObject->getSource()->getAllOptions($addEmptyOption);
        }

        if ($this->getInputType() == 'boolean' && count($selectOptions) == 0) {
            $selectOptions = $this->yesnoOptions->toOptionArray();
        }

        $key = 'value_select_options';

        if (!$this->hasData($key)) {
            $this->setData($key, $selectOptions);
        }

        return $this->getData($key);
    }

    /**
     * Collect validated attributes
     *
     * @param \Magento\Customer\Model\ResourceModel\Customer\Collection $productCollection
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        $attribute = $this->getAttribute();
        $productCollection->addAttributeToSelect($attribute, 'left');
        $attributes = $this->getRule()->getCollectedAttributes();
        $attributes[$attribute] = true;
        $this->getRule()->setCollectedAttributes($attributes);

        return $this;
    }
}
