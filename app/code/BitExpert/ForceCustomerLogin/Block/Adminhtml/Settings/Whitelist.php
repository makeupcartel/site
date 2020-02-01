<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Block\Adminhtml\Settings;

/**
 * Class Whitelist
 *
 * @package BitExpert\ForceCustomerLogin\Block\Adminhtml\Settings
 * @codingStandardsIgnoreFile
 */
class Whitelist extends \Magento\Backend\Block\Widget\Container
{
    /**
     * {@inheritDoc}
     */
    protected function _prepareLayout()
    {
        $restoreDefautsButtonProps = [
            'id' => 'restore_defaults',
            'label' => __('Restore Defaults'),
            'class' => 'primary add',
            'button_class' => '',
            'onclick' => "setLocation('" . $this->getRestoreDefaultsUrl() . "')",
            'class_name' => 'Magento\Backend\Block\Widget\Button'
        ];
        $this->buttonList->add('restore_defaults', $restoreDefautsButtonProps);

        $addButtonProps = [
            'id' => 'add_new_entry',
            'label' => __('Add Entry'),
            'class' => 'primary add',
            'button_class' => '',
            'onclick' => "setLocation('" . $this->getCreateUrl() . "')",
            'class_name' => 'Magento\Backend\Block\Widget\Button'
        ];
        $this->buttonList->add('add_new', $addButtonProps);

        return parent::_prepareLayout();
    }

    /**
     * Retrieve restore defaults url
     *
     * @return string
     */
    protected function getRestoreDefaultsUrl()
    {
        return $this->getUrl(
            'ForceCustomerLogin/Manage/RestoreDefault'
        );
    }

    /**
     * Retrieve create url
     *
     * @return string
     */
    protected function getCreateUrl()
    {
        return $this->getUrl(
            'ForceCustomerLogin/Manage/Create'
        );
    }
}
