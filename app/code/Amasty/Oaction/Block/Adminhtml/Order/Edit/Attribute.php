<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Oaction
 */


// @codingStandardsIgnoreFile

namespace Amasty\Oaction\Block\Adminhtml\Order\Edit;

use Amasty\Oaction\Model\OrderAttributesChecker;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Sales\Model\OrderFactory;

class Attribute extends \Magento\Catalog\Block\Adminhtml\Form
{
    const DEFAULT_TIME_FORMAT = 'h:mm a';

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var OrderAttributesChecker
     */
    private $orderAttributesChecker;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        OrderFactory $orderFactory,
        OrderAttributesChecker $orderAttributesChecker,
        array $data = []
    ) {
        $this->orderFactory = $orderFactory;
        $this->request = $context->getRequest();
        $this->orderAttributesChecker = $orderAttributesChecker;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->getToolbar()->addChild(
            'back_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label'   => __('Back'),
                'onclick' => 'setLocation(\'' . $this->getUrl(
                        'sales/order/',
                        ['store' => $this->getRequest()->getParam('store', 0)]
                    ) . '\')',
                'class'   => 'back'
            ]
        );

        $this->getToolbar()->addChild(
            'reset_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label'   => __('Reset'),
                'onclick' => 'setLocation(\'' . $this->getUrl(
                        'amasty_oaction/massaction/attribute_edit',
                        ['_current' => true]
                    ) . '\')',
                'class'   => 'reset'
            ]
        );

        $this->getToolbar()->addChild(
            'save_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label'          => __('Save'),
                'class'          => 'save primary',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save', 'target' => '#attributes-edit-form']],
                ]
            ]
        );
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setShowGlobalIcon(true);
    }

    /**
     * @return void
     */
    protected function _prepareForm()
    {
        try {
            $this->orderAttributesChecker->isModuleExist();

            /** @var \Magento\Framework\Data\Form $form */
            $form = $this->_formFactory->create();
            $fieldset = $form->addFieldset('fields', ['legend' => __('Attributes')]);
            $attributes = $this->getAttributes();
            /**
             * Initialize product object as form property
             * for using it in elements generation
             */
            $form->setDataObject($this->orderFactory->create());
            $this->_setFieldset($attributes, $fieldset);

            $defaultValues = [];

            foreach ($attributes as $attribute) {
                if ($defaultValue = $attribute->getDefaultValue()) {
                    $code = $attribute->getAttributeCode();

                    switch ($attribute->getFrontendInput()) {
                        case 'multiselect':
                        case 'checkboxes':
                            $defaultValues[$code] = explode(',', $defaultValue);
                            break;
                        default:
                            $defaultValues[$code] = $defaultValue;
                            break;
                    }
                }
            }

            if ($defaultValues) {
                $form->setValues($defaultValues);
            }

            $form->setFieldNameSuffix('attributes');
            $this->setForm($form);
        } catch (LocalizedException $e) {
            return parent::_prepareForm();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _applyTypeSpecificConfig($inputType, $element, \Magento\Eav\Model\Entity\Attribute $attribute)
    {
        $configProvider = ObjectManager::getInstance()->get(\Amasty\Orderattr\Model\ConfigProvider::class);

        switch ($inputType) {
            case 'boolean':
                $element->setValues($attribute->getSource()->getAllOptions());
                break;
            case 'select':
                $values = $attribute->getSource()->getAllOptions(false, true);
                array_unshift($values, ['label' => ' ', 'value' => '']);
                $element->setValues($values);
                break;
            case 'date':
                $element->addClass('date-calendar');
                $element->setDateFormat($configProvider->getDateFormatJs());
                break;
            case 'datetime':
                $element->addClass('date-calendar');
                $element->setDateFormat($configProvider->getDateFormatJs());
                $element->setTimeFormat($configProvider->getTimeFormatJs());
                break;
            case 'multiselect':
            case 'checkboxes':
                $attributeCode = $attribute->getAttributeCode();
                $element->setValues($attribute->getSource()->getAllOptions(false, true));
                $element->setName($attributeCode . '[]');
                $element->setId($attributeCode);
                break;
            case 'radios':
                $element->setValues($attribute->getSource()->getAllOptions(false, true));
                $element->setId($attribute->getAttributeCode() . '_');
                break;
            default:
                break;
        }
    }

    /**
     * Retrieve attributes for product mass update
     *
     * @return \Magento\Framework\DataObject[]
     */
    public function getAttributes()
    {
        try {
            $this->orderAttributesChecker->isModuleExist();

            return ObjectManager::getInstance()
                ->create(\Amasty\Orderattr\Model\ResourceModel\Attribute\Collection::class)->getItems();
        } catch (LocalizedException $e) {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _getAdditionalElementTypes()
    {
        return [
            'datetime' => 'date',
            'boolean'  => 'select'
        ];
    }

    /**
     * Custom additional element html
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getAdditionalElementHtml($element)
    {
        // Add name attribute to checkboxes that correspond to multiselect elements
        $nameAttributeHtml = $element->getExtType() === 'multiple' ? 'name="' . $element->getId() . '_checkbox"' : '';
        $elementId = $element->getId();
        $dataAttribute = "data-disable='{$elementId}'";
        $dataCheckboxName = "toggle_" . "{$elementId}";
        $checkboxLabel = __('Change');
        $html = <<<HTML
<span class="attribute-change-checkbox">
    <input type="checkbox" id="$dataCheckboxName" name="$dataCheckboxName" 
    class="checkbox_toggle" elementid="{$elementId}" $nameAttributeHtml $dataAttribute />
    <label class="label" for="$dataCheckboxName">
        {$checkboxLabel}
    </label>
</span>
HTML;

        return $html;
    }

    /**
     * @return string
     */
    public function getValidationUrl()
    {
        return $this->getUrl('*/*/attribute_validate', ['_current' => true]);
    }

    /**
     * Get save url
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/attribute_save', ['store' => $this->getSelectedStoreId()]);
    }

    /**
     * @return int
     */
    private function getSelectedStoreId()
    {
        return (int)$this->request->getParam('store', \Magento\Store\Model\Store::DEFAULT_STORE_ID);
    }
}
