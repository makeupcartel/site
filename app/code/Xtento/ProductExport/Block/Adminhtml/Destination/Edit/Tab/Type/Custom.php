<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2016-04-14T15:37:35+00:00
 * File:          app/code/Xtento/ProductExport/Block/Adminhtml/Destination/Edit/Tab/Type/Custom.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Block\Adminhtml\Destination\Edit\Tab\Type;

class Custom extends AbstractType
{
    // Custom Type Configuration
    public function getFields(\Magento\Framework\Data\Form $form)
    {
        $fieldset = $form->addFieldset('config_fieldset', [
            'legend' => __('Custom Type Configuration'),
            'class' => 'fieldset-wide'
        ]
        );

        $fieldset->addField('custom_class', 'text', [
            'label' => __('Custom Class Identifier'),
            'name' => 'custom_class',
            'note' => __('You can set up an own class in our (or another) module which gets called when exporting. The saveFiles($fileArray ($filename => $contents)) function would be called in your class. If your class is called \Xtento\ProductExport\Model\Destination\Myclass then the identifier to enter here would be \Xtento\ProductExport\Model\Destination\Myclass'),
            'required' => true
        ]
        );
    }
}