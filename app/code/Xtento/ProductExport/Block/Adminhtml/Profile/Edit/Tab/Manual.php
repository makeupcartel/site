<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2016-04-15T19:34:19+00:00
 * File:          app/code/Xtento/ProductExport/Block/Adminhtml/Profile/Edit/Tab/Manual.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Block\Adminhtml\Profile\Edit\Tab;

class Manual extends \Xtento\ProductExport\Block\Adminhtml\Widget\Tab implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $yesNo;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Config\Model\Config\Source\Yesno $yesNo
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Config\Model\Config\Source\Yesno $yesNo,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->yesNo = $yesNo;
        parent::__construct($context, $registry, $formFactory, $data);
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

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        
        $fieldset = $form->addFieldset('manual_fieldset', [
            'legend' => __('Manual Export Settings'),
            'class' => 'fieldset-wide',
        ]
        );

        $fieldset->addField('manual_export_enabled', 'select', [
            'label' => __('Manual Export enabled'),
            'name' => 'manual_export_enabled',
            'values' => $this->yesNo->toOptionArray(),
            'note' => __('If set to "No", this profile won\'t show up for manual exports at Products > Product Export > Manual Export and in the Products grid.')
        ]
        );

        $fieldset->addField('save_files_manual_export', 'select', [
            'label' => __('Save files on destinations for manual exports'),
            'name' => 'save_files_manual_export',
            'values' => $this->yesNo->toOptionArray(),
            'note' => __('Do you want to save exported files on the configured export destinations when exporting manually? Or do you just want them to be saved on the configured export destinations for automatic exports?')
        ]
        );

        $fieldset->addField('start_download_manual_export', 'select', [
            'label' => __('Serve files to browser after exporting manually'),
            'name' => 'start_download_manual_export',
            'values' => $this->yesNo->toOptionArray(),
            'note' => __('When exporting manually from the grid or "Manual Export" screen, if set to "Yes", the exported file will be served to the browser automatically after exporting. Ultimately this is controlled whether you check the "Serve file to browser after exporting" checkbox on the manual export screen or not.')
        ]
        );

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
        return __('Manual Export');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Manual Export');
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