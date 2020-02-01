<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2016-04-14T15:37:35+00:00
 * File:          app/code/Xtento/ProductExport/Block/Adminhtml/Destination/Edit/Tab/Type/Local.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Block\Adminhtml\Destination\Edit\Tab\Type;

class Local extends AbstractType
{
    // Local Directory Configuration
    public function getFields(\Magento\Framework\Data\Form $form)
    {
        $fieldset = $form->addFieldset(
            'config_fieldset',
            [
                'legend' => __('Local Directory Configuration'),
            ]
        );

        $fieldset->addField(
            'path',
            'text',
            [
                'label' => __('Export Directory'),
                'name' => 'path',
                'note' => __(
                    'Path to the directory where the exported file will be saved. Use an absolute path or specify a path relative to the Magento root directory by putting a dot at the beginning. Example to export into the var/export/ directory located in the root directory of Magento: ./var/export/  Example to export into an absolute directory: /var/www/test/ would export into the absolute path /var/www/test (and not located in the Magento installation)'
                ),
                'required' => true
            ]
        );
    }
}