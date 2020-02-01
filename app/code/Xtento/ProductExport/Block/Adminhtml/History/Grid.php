<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2018-08-22T10:50:44+00:00
 * File:          app/code/Xtento/ProductExport/Block/Adminhtml/History/Grid.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Block\Adminhtml\History;

class Grid extends \Magento\Backend\Block\Widget\Grid
{
    /**
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _construct()
    {
        parent::_construct();
        if ($this->getRequest()->getParam('ajax_enabled', 0) == 1) {
            $this->setData('use_ajax', true);
            $this->setData('grid_url', $this->getUrl('*/history/grid', ['_current' => 1]));
        } else {
            $this->setData('use_ajax', false);
        }
    }

    protected function getFormMessages()
    {
        $formMessages = [
            [
                'type' => 'notice',
                'message' => __(
                    "Exported objects get logged here. You can see when an object was exported. Look up the execution log entry to see why. You can also delete objects here and have them re-exported if \"Export only new objects\" is set to \"Yes\"."
                )
            ]
        ];
        return $formMessages;
    }

    protected function _toHtml()
    {
        if ($this->getRequest()->getParam('ajax')) {
            return parent::_toHtml();
        }
        return $this->_getFormMessages() . parent::_toHtml();
    }

    protected function _getFormMessages()
    {
        $html = '<div id="messages"><div class="messages">';
        foreach ($this->getFormMessages() as $formMessage) {
            $html .= '<div class="message message-' . $formMessage['type'] . ' ' . $formMessage['type'] . '"><div>' . $formMessage['message'] . '</div></div>';
        }
        $html .= '</div></div>';
        return $html;
    }
}