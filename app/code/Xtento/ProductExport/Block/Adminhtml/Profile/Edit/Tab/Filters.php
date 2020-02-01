<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2018-07-24T15:22:44+00:00
 * File:          app/code/Xtento/ProductExport/Block/Adminhtml/Profile/Edit/Tab/Filters.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Block\Adminhtml\Profile\Edit\Tab;

use Xtento\ProductExport\Model\Export;

class Filters extends \Xtento\ProductExport\Block\Adminhtml\Widget\Tab implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $yesNo;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $storeSource;

    /**
     * @var \Magento\Rule\Block\Conditions
     */
    protected $conditions;

    /**
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $rendererFieldset;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $productType;

    /**
     * @var \Xtento\ProductExport\Helper\Entity
     */
    protected $entityHelper;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\CollectionFactory
     */
    protected $customerGroupCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $productVisibility;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $productStatus;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $productAttributeCollectionFactory;

    /**
     * Filters constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Config\Model\Config\Source\Yesno $yesNo
     * @param \Magento\Store\Model\System\Store $storeSource
     * @param \Magento\Rule\Block\Conditions $conditions
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param \Magento\Catalog\Model\Product\Type $productType
     * @param \Xtento\ProductExport\Helper\Entity $entityHelper
     * @param \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $customerGroupCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $catalogProductStatus
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $productAttributeCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Config\Model\Config\Source\Yesno $yesNo,
        \Magento\Store\Model\System\Store $storeSource,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \Magento\Catalog\Model\Product\Type $productType,
        \Xtento\ProductExport\Helper\Entity $entityHelper,
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $customerGroupCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $catalogProductStatus,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $productAttributeCollectionFactory,
        array $data = []
    ) {
        $this->yesNo = $yesNo;
        $this->storeSource = $storeSource;
        $this->conditions = $conditions;
        $this->rendererFieldset = $rendererFieldset;
        $this->productType = $productType;
        $this->entityHelper = $entityHelper;
        $this->customerGroupCollectionFactory = $customerGroupCollectionFactory;
        $this->productVisibility = $productVisibility;
        $this->productStatus = $catalogProductStatus;
        $this->productAttributeCollectionFactory = $productAttributeCollectionFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function getFormMessages()
    {
        $formMessages = [];
        $formMessages[] = [
            'type' => 'notice',
            'message' => __(
                'The settings specified below will be applied to all manual and automatic exports. For manual exports, this can be changed in the "Manual Export" screen before exporting. If an %1 does not match the filters, it simply won\'t be exported.',
                $this->_coreRegistry->registry('productexport_profile')->getEntity()
            )
        ];
        // Show a warning if "Performance Settings" is used
        $attrsToSelect = $this->_coreRegistry->registry('productexport_profile')->getAttributesToSelect();
        array_shift($attrsToSelect);
        if (is_array($attrsToSelect) && count($attrsToSelect) > 0) {
            $formMessages[] = [
                'type' => 'warning',
                'message' => __(
                    'You have selected attributes in the "Performance Settings" section of the import profile. This means, only these product attributes will be available in the export. This message can be ignored if you know what you are doing, but if you think your products are missing fields in the export, then this may be caused by the "Performance Settings" attributes to export that you selected.',
                    $this->_coreRegistry->registry('productexport_profile')->getEntity()
                )
            ];
        }
        return $formMessages;
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('productexport_profile');
        if (!$model->getId()) {
            return $this;
        }
        $entity = $model->getEntity();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset(
            'store',
            ['legend' => __('Store View &amp; Customer Group'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField(
            'store_id',
            'select',
            [
                'label' => __('Store View'),
                'name' => 'store_id',
                'values' => array_merge_recursive(
                    [['value' => '', 'label' => __('--- Default Values ---')]],
                    $this->storeSource->getStoreValuesForForm()
                ),
                'note' => __(
                    'Values for attributes retrieved for %1 will be fetched from this store view. <br/><strong>Attention:</strong> If no store view is selected, no SEO / rewritten %2 URLs will be exported,<br/>as the extension doesn\'t know which store the %3 you\'re exporting belongs to. It\'s recommended to set a store view here.',
                    $this->entityHelper->getPluralEntityName(
                        $model->getEntity()
                    ),
                    $model->getEntity(),
                    $model->getEntity()
                ),
            ]
        );

        if ($model->getEntity() !== \Xtento\ProductExport\Model\Export::ENTITY_REVIEW) {
            $fieldset->addField(
                'customer_group_id',
                'select',
                [
                    'label' => __('Customer Group'),
                    'name' => 'customer_group_id',
                    'values' => array_merge_recursive(
                        [['value' => '0', 'label' => __('--- Not logged in ---')]],
                        $this->customerGroupCollectionFactory->create()->addFieldToFilter(
                            'customer_group_id',
                            ['gt' => 0]
                        )->load()->toOptionArray()
                    ),
                    'note' => __('Prices will be fetched for this customer group.'),
                ]
            );
        }

        $fieldset = $form->addFieldset(
            'object_filters',
            ['legend' => __('%1 Filters', ucwords($model->getEntity())), 'class' => 'fieldset-wide']
        );

        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $fieldset->addField(
            'export_filter_datefrom',
            'date',
            [
                'label' => __('Date From'),
                'name' => 'export_filter_datefrom',
                'date_format' => $dateFormat,
                'image' => $this->getViewFileUrl('Magento_Theme::calendar.png'),
                'note' => __('Export only %1 created after date X (including day X).', $this->entityHelper->getPluralEntityName($entity)),
                'class' => 'validate-date'
            ]
        );

        $fieldset->addField(
            'export_filter_dateto',
            'date',
            [
                'label' => __('Date To'),
                'name' => 'export_filter_dateto',
                'date_format' => $dateFormat,
                'image' => $this->getViewFileUrl('Magento_Theme::calendar.png'),
                'note' => __('Export only %1 created before date X (including day X).', $this->entityHelper->getPluralEntityName($entity)),
                'class' => 'validate-date'
            ]
        );

        $fieldset->addField(
            'export_filter_last_x_days',
            'text',
            [
                'label' => __('Created during the last X days'),
                'name' => 'export_filter_last_x_days',
                'maxlength' => 5,
                'style' => 'width: 70px !important;" min="0',
                'note' => __(
                    'Export only %1 created during the last X days (including day X). Only enter numbers here, nothing else. Leave empty if no "created during the last X days" filter should be applied.',
                    $this->entityHelper->getPluralEntityName($entity)
                )
            ]
        )->setType('number');

        if ($model->getEntity() !== \Xtento\ProductExport\Model\Export::ENTITY_REVIEW) {
            $fieldset->addField(
                'export_filter_updated_last_x_minutes',
                'text',
                [
                    'label' => __('Updated during the last X minutes'),
                    'name' => 'export_filter_updated_last_x_minutes',
                    'maxlength' => 5,
                    'style' => 'width: 70px !important;" min="1',
                    'note' => __(
                        'Export only %1 updated during the last X minutes. Only enter numbers here, nothing else. Leave empty if no "created during the last X days" filter should be applied.',
                        $this->entityHelper->getPluralEntityName($entity)
                    )
                ]
            )->setType('number');
        }

        $fieldset->addField(
            'export_filter_new_only',
            'select',
            [
                'label' => __('Export only new %1', $this->entityHelper->getPluralEntityName($entity)),
                'name' => 'export_filter_new_only',
                'values' => $this->yesNo->toOptionArray(),
                'note' => __(
                    'Regardless whether you\'re using manual, cronjob or the event-based export, if set to yes, this setting will make sure every %1 gets exported only ONCE by this profile. This means, even if another export event gets called, if the %2 has been already exported by this profile, it won\'t be exported again. You can "reset" exported objects in the "Profile Export History" tab.',
                    $this->entityHelper->getPluralEntityName($entity),
                    $entity
                )
            ]
        );

        if ($model->getEntity() == Export::ENTITY_PRODUCT) {

            $fieldset = $form->addFieldset(
                'item_filters',
                ['legend' => __('Advanced Product Filters'), 'class' => 'fieldset-wide']
            );

            $fieldset->addField(
                'export_filter_instock_only',
                'select',
                [
                    'label' => __('Export *only* in stock products'),
                    'name' => 'export_filter_instock_only',
                    'values' => $this->yesNo->toOptionArray(),
                    'note' => __('If set to yes, only products which are in stock will be exported.')
                ]
            );

            $productVisibilities = $this->productVisibility->getAllOptions();
            $fieldset->addField(
                'export_filter_product_visibility',
                'multiselect',
                [
                    'label' => __('Product visibilities to export'),
                    'name' => 'export_filter_product_visibility',
                    'values' => array_merge_recursive(
                        [['value' => '', 'label' => __('--- All product visibilities ---')]],
                        $productVisibilities
                    ),
                    'note' => __('Only products where the selected visibility value matches will be exported.'),
                ]
            );

            $productStatuses = $this->productStatus->getAllOptions();
            $fieldset->addField(
                'export_filter_product_status',
                'multiselect',
                [
                    'label' => __('Product status to export'),
                    'name' => 'export_filter_product_status',
                    'values' => array_merge_recursive(
                        [['value' => '', 'label' => __('--- All product statuses ---')]],
                        $productStatuses
                    ),
                    'note' => __('Only products where the selected status value matches will be exported.'),
                ]
            );

            $fieldset->addField(
                'export_filter_product_type',
                'multiselect',
                [
                    'label' => __('Hidden Product Types'),
                    'name' => 'export_filter_product_type',
                    'values' => array_merge_recursive(
                        [['value' => '', 'label' => __('--- No hidden product types ---')]],
                        $this->productType->getOptions()
                    ),
                    'note' => __(
                        'The selected product types won\'t be exported and won\'t show up in the output format for this profile. You can still fetch information from the parent product in the XSL Template using the <i>parent_item/</i> node.'
                    )
                ]
            );

            $renderer = $this->rendererFieldset->setTemplate(
                'Magento_CatalogRule::promo/fieldset.phtml'
            )->setNewChildUrl(
                $this->getUrl(
                    'xtento_productexport/profile/newConditionHtml/form/rule_conditions_fieldset',
                    ['profile_id' => $model->getId()]
                )
            );

            $fieldset = $form->addFieldset(
                'rule_conditions_fieldset',
                [
                    'legend' => __(
                        'Additional filters: Export %1 only if the following conditions are met (Attention: When exporting many %2, set up the filter in the XSL Template - much faster)',
                        $entity,
                        $this->entityHelper->getPluralEntityName($entity)
                    ),
                ]
            )->setRenderer($renderer);

            $fieldset->addField(
                'conditions',
                'text',
                [
                    'name' => 'conditions',
                    'label' => __('Conditions'),
                    'title' => __('Conditions'),
                ]
            )->setRule($model)->setRenderer($this->conditions);


            $fieldset = $form->addFieldset(
                'performance_settings',
                ['legend' => __('Performance Settings'), 'class' => 'fieldset-wide']
            );

            $availableAttributes = [['value' => '', 'label' => __('--- All attributes ---')]];
            $productAttributes = $this->productAttributeCollectionFactory->create()
                ->setOrder('main_table.frontend_label', 'asc')
                ->load();
            foreach ($productAttributes as $productAttribute) {
                if ($productAttribute->getFrontendLabel()) {
                    $availableAttributes[] = ['label' => sprintf("%s [%s]", $productAttribute->getFrontendLabel(), $productAttribute->getAttributeCode()), 'value' => $productAttribute->getAttributeCode()];
                }
            }
            $fieldset->addField('attributes_to_select', 'multiselect', [
                'label' => __('Product attributes to export'),
                'name' => 'attributes_to_select',
                'values' => $availableAttributes,
                'note' => __('This can be used to speed up the export. Only the attributes selected below will be made available when exporting then. This is especially helpful if you have a lot product attributes. Select multiple attributes using the CTRL/SHIFT buttons on your keyboard.'),
                'style' => 'width: auto; max-width: 500px;'
            ]
            );
        }


        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Stores & Filters');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Stores & Filters');
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
}