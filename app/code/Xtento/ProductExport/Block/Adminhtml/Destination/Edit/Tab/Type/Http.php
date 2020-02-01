<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2016-04-14T15:37:35+00:00
 * File:          app/code/Xtento/ProductExport/Block/Adminhtml/Destination/Edit/Tab/Type/Http.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Block\Adminhtml\Destination\Edit\Tab\Type;

class Http extends AbstractType
{
    // HTTP Configuration
    public function getFields(\Magento\Framework\Data\Form $form)
    {
        $fieldset = $form->addFieldset('config_fieldset', [
            'legend' => __('HTTP Configuration'),
            'class' => 'fieldset-wide'
        ]
        );

        $fieldset->addField('http_note', 'note', [
            'text' => __('<b>Instructions</b>: To export data to a HTTP server, please follow the following steps:<br>1) Go into the <i>app/code/Xtento/ProductExport/Model/Destination/</i> directory and rename the file "Http.php.sample" to "Http.php"<br>2) Enter the function name you want to call in the Http.php class in the field below.<br>3) Open the Http.php file and add a function that matches the function name you entered. This function will be called by this destination upon exporting then.<br><br><b>Example:</b> If you enter server1 in the function name field below, a method called server1($fileArray) must exist in the Http.php file. This way multiple HTTP servers can be added to the HTTP class, and can be called from different export destination, separated by the function name that is called. The function you add then gets called whenever this destination is executed by an export profile.')
        ]
        );

        $fieldset->addField('custom_function', 'text', [
            'label' => __('Custom Function'),
            'name' => 'custom_function',
            'note' => __('Please make sure the function you enter exists like this in the app/code/Xtento/ProductExport/Model/Destination/Http.php file:<br>public function <i>yourFunctionName</i>($fileArray) { ... }'),
            'required' => true
        ]
        );
    }
}