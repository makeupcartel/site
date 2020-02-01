<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    \Unirgy\RapidFlow
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\RapidFlow\Block\Adminhtml\Profile\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\Form as DataForm;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Unirgy\RapidFlow\Model\Source;

class File extends Generic
{
    /**
     * @var Source
     */
    protected $_rapidFlowSource;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Source $rapidFlowSource,
        array $data = []
    ) {
        $this->_rapidFlowSource = $rapidFlowSource;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function _prepareForm()
    {
        $source = $this->_rapidFlowSource;

        $profile = $this->_coreRegistry->registry('profile_data');

        $form = $this->_formFactory->create();
        $this->setForm($form);

        /*
                if ($profile->getDataType()=='product') {

                    $fieldset = $form->addFieldset('dirs_form', array('legend'=>__('Target Directories')));

                    $fieldset->addField('import_images_dir', 'text', array(
                        'label'     => __('Images'),
                        'name'      => 'options[dir][images]',
                        'value'     => $profile->getData('options/dir/images'),
                        'note'      => __('Leave empty for default'),
                    ));
                    $fieldset->addField('import_downloads_dir', 'text', array(
                        'label'     => __('Downloadables'),
                        'name'      => 'options[dir][downloads]',
                        'value'     => $profile->getData('options/dir/downloads'),
                        'note'      => __('Leave empty for default'),
                    ));
                }
        */


        $fieldset = $form->addFieldset('remote_options_form', ['legend' => __('Remote Server')]);

        $fieldset->addField('remote_type', 'select', [
            'label' => __('Server Type'),
            'name' => 'options[remote][type]',
            'values' => $source->setPath('remote_type')->toOptionArray(),
            'value' => $profile->getData('options/remote/type'),
        ]);

        $fieldset->addField('remote_host', 'text', [
            'label' => __('Host'),
            'name' => 'options[remote][host]',
            'value' => $profile->getData('options/remote/host'),
        ]);

        $fieldset->addField('remote_port', 'text', [
            'label' => __('Port'),
            'name' => 'options[remote][port]',
            'value' => $profile->getData('options/remote/port'),
            'note' => __('Leave empty for default'),
        ]);

        $fieldset->addField('remote_username', 'text', [
            'label' => __('User Name'),
            'name' => 'options[remote][username]',
            'value' => $profile->getData('options/remote/username'),
        ]);

        $fieldset->addField('remote_password', 'text', [
            'label' => __('Password'),
            'name' => 'options[remote][password]',
            'value' => $profile->getData('options/remote/password'),
        ]);

        $fieldset->addField('remote_path', 'text', [
            'label' => __('Path (Folder)'),
            'name' => 'options[remote][path]',
            'value' => $profile->getData('options/remote/path'),
        ]);

        $ftpPassive = $profile->getData('options/remote/ftp_passive');
        $fieldset->addField('ftp_passive', 'select', [
            'label' => __('Ftp Passive Mode'),
            'name' => 'options[remote][ftp_passive]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => (null == $ftpPassive) ? 1 : $ftpPassive,
        ]);

        $ftpMode = $profile->getData('options/remote/ftp_file_mode');
        $fieldset->addField('ftp_file_mode', 'select', [
            'label' => __('Ftp File Mode'),
            'name' => 'options[remote][ftp_file_mode]',
            'values' => $source->setPath('ftp_file_mode')->toOptionArray(),
            'value' => (null == $ftpMode) ? FTP_BINARY : $ftpMode,
        ]);

        $fieldset->addField('sftp_rsa_file', 'text', [
            'label' => __('SFTP Private Key File'),
            'name' => 'options[remote][sftp_rsa_file]',
            'value' => $profile->getData('options/remote/sftp_rsa_file'),
            'note' => __('Absolute path to private key file on website server')
        ]);

        $fieldset->addField('sftp_rsa_passphrase', 'password', [
            'label' => __('SFTP Private Key Passphrase'),
            'name' => 'options[remote][sftp_rsa_passphrase]',
            'note' => __('Leave empty for no password')
        ]);

        $fieldset->addField('ssh_timeout', 'text', [
            'label' => __('SFTP Connection Timeout'),
            'name' => 'options[remote][ssh_timeout]',
            'value' => $profile->getData('options/remote/ssh_timeout'),
        ]);

        /*
                $fieldset = $form->addFieldset('compress_optionsform', array('legend'=>__('Compression')));

                $fieldset->addField('compress_type', 'select', array(
                    'label'     => __('Compression Type'),
                    'name'      => 'options[compress][type]',
                    'values'    => $source->setPath('compress_type')->toOptionArray(),
                    'value'     => $profile->getData('options/compress/type'),
                ));
        */
        return parent::_prepareForm();
    }
}
