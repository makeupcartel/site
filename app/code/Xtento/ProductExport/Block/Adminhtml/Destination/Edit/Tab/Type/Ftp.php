<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2016-04-14T15:37:56+00:00
 * File:          app/code/Xtento/ProductExport/Block/Adminhtml/Destination/Edit/Tab/Type/Ftp.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Block\Adminhtml\Destination\Edit\Tab\Type;

class Ftp extends AbstractType
{
    // FTP Configuration
    public function getFields(\Magento\Framework\Data\Form $form, $type = 'FTP')
    {
        $model = $this->registry->registry('productexport_destination');
        if ($type == 'FTP') {
            $fieldset = $form->addFieldset('config_fieldset', [
                'legend' => __('FTP Configuration'),
            ]
            );
        } else {
            // SFTP
            $fieldset = $form->addFieldset('config_fieldset', [
                'legend' => __('SFTP Configuration'),
            ]
            );
            $fieldset->addField('sftp_note', 'note', [
                'text' => __('<strong>Important</strong>: Only SFTPv3 servers are supported. Please make sure the server you\'re trying to connect to is a SFTPv3 server.')
            ]
            );
        }

        $fieldset->addField('hostname', 'text', [
            'label' => __('IP or Hostname'),
            'name' => 'hostname',
            'required' => true,
        ]
        );
        if ($type == 'FTP') {
            $fieldset->addField('ftp_type', 'select', [
                'label' => __('Server Type'),
                'name' => 'ftp_type',
                'options' => [
                    \Xtento\ProductExport\Model\Destination\Ftp::TYPE_FTP => 'FTP',
                    \Xtento\ProductExport\Model\Destination\Ftp::TYPE_FTPS => 'FTPS ("FTP SSL")',
                ],
                'note' => __('FTPS is only available if PHP has been compiled with OpenSSL support. Only some server versions are supported, support is limited by PHP.')
            ]
            );
        }
        $fieldset->addField('port', 'text', [
            'label' => __('Port'),
            'name' => 'port',
            'note' => __('Default Port: %1', ($type == 'FTP') ? 21 : 22),
            'class' => 'validate-number',
            'required' => true,
        ]
        );
        $fieldset->addField('username', 'text', [
            'label' => __('Username'),
            'name' => 'username',
            'required' => true,
        ]
        );
        $fieldset->addField('new_password', 'obscure', [
            'label' => __('Password'),
            'name' => 'new_password',
            'required' => true,
        ]
        );
        $model->setNewPassword(($model->getPassword()) ? '******' : '');
        $fieldset->addField('timeout', 'text', [
            'label' => __('Timeout'),
            'name' => 'timeout',
            'note' => __('Timeout in seconds after which the connection to the server fails'),
            'required' => true,
            'class' => 'validate-number'
        ]
        );
        if ($type == 'FTP') {
            $fieldset->addField('ftp_pasv', 'select', [
                'label' => __('Enable Passive Mode'),
                'name' => 'ftp_pasv',
                'values' => $this->yesNo->toOptionArray(),
                'note' => __('If your server is behind a firewall, or if the extension has problems uploading the exported files, please set this to "Yes".')
            ]
            );
        }
        $fieldset->addField('path', 'text', [
            'label' => __('Export Directory'),
            'name' => 'path',
            'note' => __('This is the absolute path to the directory on the server where files will be uploaded to. This directory has to exist on the FTP server.'),
            'required' => true,
        ]
        );
    }
}