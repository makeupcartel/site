<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Block\Adminhtml\Location\Edit\Tab;

use Amasty\Storelocator\Model\WysiwygConfig;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Attribute extends Generic implements TabInterface
{
    /**
     * @var \Amasty\Storelocator\Model\ResourceModel\Attribute\Collection
     */
    protected $attributeCollection;

    /**
     * @var \Amasty\Storelocator\Model\ResourceModel\Options\CollectionFactory
     */
    protected $optionsCollection;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    protected $serializer;

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Store Attributes');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Store Attributes');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $store
     * @param WysiwygConfig $wysiwygConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Amasty\Storelocator\Model\ResourceModel\Attribute\Collection $attributeCollection,
        \Amasty\Storelocator\Model\ResourceModel\Options\CollectionFactory $optionsCollection,
        \Amasty\Base\Model\Serializer $serializer,
        WysiwygConfig $wysiwygConfig,
        array $data = []
    ) {
        $this->attributeCollection = $attributeCollection;
        $this->optionsCollection = $optionsCollection;
        $this->serializer = $serializer;
        $this->wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('attributes', ['legend' => __('Store Attributes')]);

        $collection = $this->attributeCollection;
        $model = $this->_coreRegistry->registry('current_amasty_storelocator_location');
        $attributes = $model->getAttributes();

        if ($collection->getSize()) {
            foreach ($collection as $model) {
                $code = $model->getAttributeCode();
                $type = $model->getFrontendInput();
                if ($type == 'texteditor') {
                    $type = 'textarea';
                }
                $values = [];
                $value = '';
                if ($type == 'multiselect'
                    || $type == 'select'
                ) {
                    if ($type == 'select') {
                        $values = [['label' => __('Please Select'), 'value' => '']];
                    }

                    $optionsCollection = $this->optionsCollection->create();
                    $options = $optionsCollection->addFieldToFilter('attribute_id', ['eq' => (int)$model->getId()]);
                    foreach ($options as $option) {
                        $optionValues = $this->serializer->unserialize($option['options_serialized']);
                        if (isset($optionValues[0])) {
                            if ($option['is_default']) {
                                if (is_string($value)) {
                                    $value = [];
                                }
                                $value[] = $option->getId();
                            }
                            $values[] = ['label' => $optionValues[0], 'value' => $option->getId()];
                        }
                    }
                }

                if (!empty($attributes)) {
                    foreach ($attributes as $attribute) {
                        if ($attribute['attribute_id'] == $model->getId()) {
                            if (isset($attribute['value']) && $attribute['value']) {
                                $value = $attribute['value'];
                                if ($type == 'multiselect') {
                                    $value = explode(',', $value);
                                }
                                break;
                            }
                        }
                    }
                }

                if ($type == 'boolean') {
                    $type = 'select';
                    $values = [
                        ['label' => __('Please Select'), 'value' => ''],
                        ['label' => __('No'), 'value' => 0],
                        ['label' => __('Yes'), 'value' => 1]
                    ];
                }

                $fieldset->addField(
                    $code,
                    $type,
                    [
                        'name' => 'store_attribute[' . $model->getId() . ']',
                        'label' => $model->getFrontendLabel(),
                        'title' => $model->getFrontendLabel(),
                        'required' => $model->getIsRequired(),
                        'format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                        'values' => $values,
                        'value' => $value
                    ]
                );
            }
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
