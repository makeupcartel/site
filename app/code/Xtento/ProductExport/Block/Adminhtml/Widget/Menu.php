<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2019-05-10T19:18:39+00:00
 * File:          app/code/Xtento/ProductExport/Block/Adminhtml/Widget/Menu.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Block\Adminhtml\Widget;

use Xtento\XtCore\Helper\Utils;

class Menu extends \Magento\Backend\Block\AbstractBlock
{
    protected $menuBar;

    protected $menu = [
        'manual' => [
            'name' => 'Manual Export',
            'action_name' => '',
            'last_link' => false,
            'is_link' => true
        ],
        'log' => [
            'name' => 'Execution Log',
            'action_name' => '',
            'last_link' => false,
            'is_link' => true
        ],
        'history' => [
            'name' => 'Export History',
            'action_name' => '',
            'last_link' => false,
            'is_link' => true
        ],
        'configuration' => [
            'name' => 'Configuration',
            'last_link' => false,
            'is_link' => false,
        ],
        'profile' => [
            'name' => 'Export Profiles',
            'action_name' => '',
            'last_link' => false,
            'is_link' => true
        ],
        'destination' => [
            'name' => 'Export Destinations',
            'action_name' => '',
            'last_link' => false,
            'is_link' => true
        ],
        'tools' => [
            'name' => 'Tools',
            'action_name' => '',
            'last_link' => false,
            'is_link' => true
        ],
    ];

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $adminhtmlData;

    /**
     * @var Utils
     */
    protected $utilsHelper;

    /**
     * Menu constructor.
     *
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Backend\Helper\Data $adminhtmlData
     * @param Utils $utilsHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Helper\Data $adminhtmlData,
        Utils $utilsHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->adminhtmlData = $adminhtmlData;
        $this->utilsHelper = $utilsHelper;
    }

    protected function getMenu()
    {
        return $this->menu;
    }

    protected function _toHtml()
    {
        $title = __('Product Export Navigation');
        $this->menuBar = <<<EOT
        <style>
        .icon-head { padding-left: 0px; }
        </style>
        <div style="padding:8px; border: 1px solid #e3e3e3; background: #f8f8f8; font-size:12px;">
            {$title}&nbsp;-&nbsp;
EOT;
        foreach ($this->getMenu() as $controllerName => $entryConfig) {
            if ($entryConfig['is_link']) {
                if (!$this->_authorization->isAllowed('Xtento_ProductExport::' . $controllerName)) {
                    // No rights to see
                    continue;
                }
                $this->addMenuLink(
                    __($entryConfig['name']),
                    $controllerName,
                    $entryConfig['action_name'],
                    $entryConfig['last_link']
                );
            } else {
                $this->menuBar .= $entryConfig['name'];
                if (!$entryConfig['last_link']) {
                    $this->menuBar .= '&nbsp;|&nbsp;';
                }
            }
        }
        $this->menuBar .= '<a href="http://support.xtento.com/wiki/Magento_2_Extensions:Magento_Product_Export_Module" target="_blank" style="font-weight: bold;">' . __(
                'Get Help'
            ) . '</a>';
        $this->menuBar .= '</div>';
        if (method_exists($this->utilsHelper, 'getExtensionStatusString')) {
            // To avoid issues if someone didn't update XtCore for some reason
            $extensionStatus = $this->utilsHelper->getExtensionStatusString('Xtento_ProductExport', 'Xtento\ProductExport\Model\System\Config\Backend\Server');
            if (!empty($extensionStatus)) {
                $this->menuBar .= '<div style="padding:8px; margin-bottom: 10px; border: 1px solid #e3e3e3; border-top:0; background: #f8f8f8; font-size:12px;">';
                $this->menuBar .= '<div style="float:right;"><a href="https://www.xtento.com/" target="_blank" style="text-decoration:none;color:#57585B;"><img src="//www.xtento.com/media/images/extension_logo.png" alt="XTENTO" height="20" style="vertical-align:middle;"/> XTENTO Magento Extensions</a></div>';
                $this->menuBar .= $extensionStatus;
                $this->menuBar .= '</div>';
            } else {
                $this->menuBar .= "<br/><!--Could not retrieve extension status-->";
            }
        } else {
            $this->menuBar .= "<br/><!--Outdated Xtento_XtCore-->";
        }

        return $this->menuBar;
    }

    protected function addMenuLink($name, $controllerName, $actionName = '', $lastLink = false)
    {
        $isActive = '';
        if ($this->getRequest()->getControllerName() == $controllerName) {
            if ($actionName == '' || $this->getRequest()->getActionName() == $actionName) {
                $isActive = 'font-weight: bold;';
            }
        }
        $this->menuBar .= '<a href="' . $this->adminhtmlData->getUrl(
                '*/' . $controllerName . '/' . $actionName
            ) . '" style="' . $isActive . '">' . __(
                $name
            ) . '</a>';
        if (!$lastLink) {
            $this->menuBar .= '&nbsp;|&nbsp;';
        }
    }
}