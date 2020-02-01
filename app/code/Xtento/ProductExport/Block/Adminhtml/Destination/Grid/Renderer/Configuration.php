<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2016-04-14T15:37:35+00:00
 * File:          app/code/Xtento/ProductExport/Block/Adminhtml/Destination/Grid/Renderer/Configuration.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Block\Adminhtml\Destination\Grid\Renderer;

class Configuration extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Render destination configuration
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $configuration = [];
        if ($row->getType() == \Xtento\ProductExport\Model\Destination::TYPE_LOCAL) {
            $configuration['directory'] = $row->getPath();
        }
        if ($row->getType() == \Xtento\ProductExport\Model\Destination::TYPE_FTP || $row->getType(
            ) == \Xtento\ProductExport\Model\Destination::TYPE_SFTP
        ) {
            $configuration['server'] = $row->getHostname() . ':' . $row->getPort();
            $configuration['username'] = $row->getUsername();
            $configuration['path'] = $row->getPath();
        }
        if ($row->getType() == \Xtento\ProductExport\Model\Destination::TYPE_EMAIL) {
            $configuration['from'] = $row->getEmailSender();
            $configuration['to'] = $row->getEmailRecipient();
            $configuration['subject'] = $row->getEmailSubject();
        }
        if ($row->getType() == \Xtento\ProductExport\Model\Destination::TYPE_CUSTOM) {
            $configuration['class'] = $row->getCustomClass();
        }
        if ($row->getType() == \Xtento\ProductExport\Model\Destination::TYPE_WEBSERVICE) {
            $configuration['class'] = 'Webservice';
            $configuration['function'] = $row->getCustomFunction();
        }
        if (!empty($configuration)) {
            $configurationHtml = '';
            foreach ($configuration as $key => $value) {
                $configurationHtml .= __(ucfirst($key)) . ': <i>' . $this->escapeHtml($value) . '</i><br/>';
            }
            return $configurationHtml;
        } else {
            return '---';
        }
    }
}
