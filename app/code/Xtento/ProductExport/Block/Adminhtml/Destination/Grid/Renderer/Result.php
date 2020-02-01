<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2016-04-14T15:37:35+00:00
 * File:          app/code/Xtento/ProductExport/Block/Adminhtml/Destination/Grid/Renderer/Result.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Block\Adminhtml\Destination\Grid\Renderer;

class Result extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Render destination status
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $class = 'grid-severity-major';
        $text = __('No Result');
        switch ($this->_getValue($row)) {
            case 0:
                $class = 'grid-severity-critical';
                $text = __('Failed');
                break;
            case 1:
                $class = 'grid-severity-notice';
                $text = __('Success');
                break;
        }
        return '<span class="' . $class . '"><span>' . $text . '</span></span>';
    }
}
