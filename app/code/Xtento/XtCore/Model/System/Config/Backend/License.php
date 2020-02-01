<?php

/**
 * Product:       Xtento_XtCore
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2017-08-16T08:52:13+00:00
 * File:          app/code/Xtento/XtCore/Model/System/Config/Backend/License.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\XtCore\Model\System\Config\Backend;

class License extends \Magento\Framework\App\Config\Value
{
    public function beforeSave()
    {
        $this->_registry->register('xtento_configuration_license_key', $this->getValue(), true);
    }
}
