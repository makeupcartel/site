<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Block\Adminhtml\Manage;

/**
 * Class Create
 *
 * @package BitExpert\ForceCustomerLogin\Block\Adminhtml\Manage
 * @codingStandardsIgnoreFile
 */
class Create extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var string
     */
    private $formIdentifier = 'create_manage_entry_form';

    /**
     * Initialize printer post create block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'whitelist_entry_id';
        $this->_blockGroup = 'BitExpert_ForceCustomerLogin';
        $this->_controller = 'adminhtml_manage';
        $this->_mode = 'create';

        parent::_construct();

        $this->updateButtonControls();
    }

    /**
     * {@inheritdoc}
     */
    public function getSaveUrl()
    {
        return $this->getUrl(
            'ForceCustomerLogin/Manage/Save',
            [
                $this->_objectId => $this->getRequest()->getParam($this->_objectId)
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBackUrl()
    {
        return $this->getUrl('ForceCustomerLogin/Manage');
    }

    /**
     */
    protected function updateButtonControls()
    {
        $this->buttonList->update(
            'save',
            'data_attribute',
            [
                'mage-init' => [
                    'button' => [
                        'event' => 'save',
                        'target' => '#' . $this->getFormIdentifier()
                    ]
                ],
            ]
        );
    }

    /**
     * @return string
     */
    protected function getFormIdentifier()
    {
        return $this->formIdentifier;
    }
}