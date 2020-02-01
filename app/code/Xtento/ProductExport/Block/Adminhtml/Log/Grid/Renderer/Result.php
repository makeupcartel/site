<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2016-04-14T15:37:35+00:00
 * File:          app/code/Xtento/ProductExport/Block/Adminhtml/Log/Grid/Renderer/Result.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Block\Adminhtml\Log\Grid\Renderer;

class Result extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row)
    {
        if ($row->getResult() === null || $row->getResult() == 0) {
            return '<span class="grid-severity-major"><span>' . __('No Result') . '</span></span>';
        } else {
            if ($row->getResult() == 1) {
                return '<span class="grid-severity-notice"><span>' . __('Success') . '</span></span>';
            } else {
                if ($row->getResult() == 2) {
                    return '<span class="grid-severity-minor"><span>' . __('Warning') . '</span></span>';
                } else {
                    if ($row->getResult() == 3) {
                        return '<span class="grid-severity-critical"><span>' . __('Failed') . '</span></span>';
                    }
                }
            }
        }
        return '';
    }
}
