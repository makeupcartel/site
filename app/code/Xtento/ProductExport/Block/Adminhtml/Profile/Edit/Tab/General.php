<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2018-11-15T10:21:16+00:00
 * File:          app/code/Xtento/ProductExport/Block/Adminhtml/Profile/Edit/Tab/General.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Block\Adminhtml\Profile\Edit\Tab;

use Xtento\ProductExport\Helper\Entity;
use Xtento\ProductExport\Model\Export;

class General extends \Xtento\ProductExport\Block\Adminhtml\Widget\Tab implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $yesNo;

    /**
     * @var \Xtento\ProductExport\Model\System\Config\Source\Export\Entity
     */
    protected $exportEntity;

    /**
     * @var Entity
     */
    protected $entityHelper;

    /**
     * General constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Config\Model\Config\Source\Yesno $yesNo
     * @param \Xtento\ProductExport\Model\System\Config\Source\Export\Entity $exportEntity
     * @param Entity $entityHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Config\Model\Config\Source\Yesno $yesNo,
        \Xtento\ProductExport\Model\System\Config\Source\Export\Entity $exportEntity,
        Entity $entityHelper,
        array $data = []
    ) {
        $this->yesNo = $yesNo;
        $this->exportEntity = $exportEntity;
        $this->entityHelper = $entityHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function getFormMessages()
    {
        $formMessages = [];
        $model = $this->_coreRegistry->registry('productexport_profile');
        if ($model->getId() && !$model->getEnabled()) {
            $formMessages[] = [
                'type' => 'warning',
                'message' => __(
                    'This profile is disabled. No automatic exports will be made and the profile won\'t show up for manual exports.'
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
        // Set default values
        if (!$model->getId()) {
            $model->setEnabled(1);
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('General Configuration'),
            ]
        );

        if ($model->getId()) {
            $fieldset->addField(
                'profile_id',
                'hidden',
                [
                    'name' => 'profile_id',
                ]
            );
        }

        $fieldset->addField(
            'name',
            'text',
            [
                'label' => __('Name'),
                'name' => 'name',
                'required' => true,
            ]
        );

        if ($model->getId()) {
            $fieldset->addField(
                'enabled',
                'select',
                [
                    'label' => __('Enabled'),
                    'name' => 'enabled',
                    'values' => $this->yesNo->toOptionArray()
                ]
            );
        }

        $entity = $fieldset->addField(
            'entity',
            'select',
            [
                'label' => __('Export Type'),
                'name' => 'entity',
                'options' => $this->exportEntity->toOptionArray(),
                'required' => true,
                'note' => __(
                    'This setting can\'t be changed after creating the profile. Add a new profile for different export types.'
                )
            ]
        );

        if ($model->getId()) {
            $entity->setDisabled(true);
        } else {
            $model->setName(__('New Profile'));
            $model->setEntity(Export::ENTITY_PRODUCT);
        }

        if (!$this->_coreRegistry->registry('productexport_profile') || !$this->_coreRegistry->registry(
                'productexport_profile'
            )->getId()
        ) {
            $fieldset->addField(
                'continue_button',
                'note',
                [
                    'text' => $this->getChildHtml('continue_button'),
                ]
            );
        }

        if ($this->_coreRegistry->registry('productexport_profile') && $this->_coreRegistry->registry(
                'productexport_profile'
            )->getId()
        ) {
            $fieldset = $form->addFieldset(
                'advanced_fieldset',
                [
                    'legend' => __('Export Settings'),
                    'class' => 'fieldset-wide',
                ]
            );

            $fieldset->addField(
                'save_files_local_copy',
                'select',
                [
                    'label' => __('Save local copies of exports'),
                    'name' => 'save_files_local_copy',
                    'values' => $this->yesNo->toOptionArray(),
                    'note' => __(
                        'If set to yes, local copies of the exported files will be saved in the ./var/productexport_bkp/ folder. If set to no, you won\'t be able to download old export files from the export/execution log.'
                    )
                ]
            );

            $fieldset->addField(
                'export_one_file_per_object',
                'select',
                [
                    'label' => __(
                        'Export each %1 separately',
                        $this->_coreRegistry->registry('productexport_profile')->getEntity()
                    ),
                    'name' => 'export_one_file_per_object',
                    'values' => $this->yesNo->toOptionArray(),
                    'note' => __(
                        'If set to yes, each %1 exported would be saved in a separate file. This means, for every %2 you export, one file will be created, with just the one %3 in there. If set to no, one file will be created with all the exported %4 in there.',
                        $this->_coreRegistry->registry('productexport_profile')->getEntity(),
                        $this->_coreRegistry->registry('productexport_profile')->getEntity(),
                        $this->_coreRegistry->registry('productexport_profile')->getEntity(),
                        $this->entityHelper->getPluralEntityName(
                            $this->_coreRegistry->registry('productexport_profile')->getEntity()
                        )
                    )
                ]
            );

            $fieldset->addField(
                'export_empty_files',
                'select',
                [
                    'label' => __('Export empty files'),
                    'name' => 'export_empty_files',
                    'values' => $this->yesNo->toOptionArray(),
                    'note' => __(
                        'If set to yes, every export will create a file. Even if 0 %1 have been exported, an empty export file will be created.',
                        $this->entityHelper->getPluralEntityName(
                            $this->_coreRegistry->registry('productexport_profile')->getEntity()
                        )
                    )
                ]
            );

            $fieldset->addField(
                'export_strip_tags',
                'select',
                [
                    'label' => __('Remove HTML in exported data'),
                    'name' => 'export_strip_tags',
                    'values' => $this->yesNo->toOptionArray(),
                    'note' => __(
                        'If set to yes, HTML/XML tags will get removed from any exported data using the PHP function strip_tags.'
                    )
                ]
            );

            $fieldset->addField(
                'export_replace_nl_br',
                'select',
                [
                    'label' => __('Replace new lines in attribute values'),
                    'name' => 'export_replace_nl_br',
                    'values' => [
                        ['value' => 3, 'label' => __('Remove new lines from attribute values')],
                        ['value' => 2, 'label' => __('Replace with a space')],
                        ['value' => 1, 'label' => __('Replace with <br />')],
                        ['value' => 0, 'label' => __('No replacing')],
                    ],
                    'note' => __('If enabled, new lines (\n, \r, \r\n) in attribute values will be replaced.')
                ]
            );

            if ($this->_coreRegistry->registry('productexport_profile')->getEntity() != \Xtento\ProductExport\Model\Export::ENTITY_REVIEW) {
                $fieldset->addField(
                    'export_url_remove_store',
                    'select',
                    [
                        'label' => __('Remove parameters from URL'),
                        'name' => 'export_url_remove_store',
                        'values' => $this->yesNo->toOptionArray(),
                        'note' => __(
                            'If set to yes, parameters (everything after "?") will be removed from exported product URLs. Only use this if you don\'t want the ___store parameter (and other parameters) to show up in your product URLs.'
                        )
                    ]
                );
                $fieldset->addField(
                    'remove_pub_folder_from_urls',
                    'select',
                    [
                        'label' => __('Remove pub folder from URLs'),
                        'name' => 'remove_pub_folder_from_urls',
                        'values' => $this->yesNo->toOptionArray(),
                        'note' => __(
                            'If set to yes, any references to the "/pub/" folder of Magento used will be removed from URLs. Recommended to be enabled unless you have an use case where the pub folder should appear in the URL.'
                        )
                    ]
                );
            }
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _prepareLayout()
    {
        $this->setChild(
            'continue_button',
            $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
                ->setData(
                    [
                        'label' => __('Continue'),
                        'data_attribute' => [
                            'mage-init' => [
                                'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                            ],
                        ],
                        'class' => 'save'
                    ]
                )
        );
        return parent::_prepareLayout();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('General Configuration');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('General Configuration');
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