<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2018-08-22T10:50:49+00:00
 * File:          app/code/Xtento/ProductExport/Block/Adminhtml/Destination/Grid.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Block\Adminhtml\Destination;

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
            $this->setData('grid_url', $this->getUrl('*/destination/grid', ['_current' => 1]));
        } else {
            $this->setData('use_ajax', false);
        }
    }

    /**
     * @var \Xtento\ProductExport\Model\ProfileFactory
     */
    protected $profileFactory;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Xtento\ProductExport\Model\ProfileFactory $profileFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Xtento\ProductExport\Model\ProfileFactory $profileFactory,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->profileFactory = $profileFactory;
    }

    protected function getProfile()
    {
        return $this->profileFactory->create()->load($this->getRequest()->getParam('id'));
    }

    public function getSelectedDestinations()
    {
        $array = explode("&", $this->getProfile()->getDestinationIds());
        return $array;
    }

    protected function getFormMessages()
    {
        $formMessages = [
            [
                'type' => 'notice',
                'message' => __('Export destinations control where exported files are sent to. Set up local directory, FTP, SFTP, etc. destinations and enable them in the export profiles "Export Destinations" tab.')
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