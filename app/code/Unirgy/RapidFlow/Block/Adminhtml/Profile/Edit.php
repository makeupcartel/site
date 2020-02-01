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

namespace Unirgy\RapidFlow\Block\Adminhtml\Profile;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;

class Edit extends Container
{
    /**
     * @var Registry
     */
    protected $_registry;

    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_registry = $registry;

        parent::__construct($context, $data);

        $this->_objectId = 'id';
        $this->_blockGroup = 'rapidflow';
        $this->_controller = 'adminhtml_profile';

        $profile = $this->_registry->registry('profile_data');
        $id = $profile->getId();

        if ($this->_scopeConfig->getValue('urapidflow/advanced/disable_changes')) {
            $this->buttonList->remove('reset');
            $this->buttonList->remove('save');
            $this->buttonList->remove('delete');
            return;
        }

        switch ($profile->getRunStatus()) {
            case 'pending':
            case 'running':
            case 'paused':
                //$this->buttonList->remove('back');
                $this->buttonList->remove('reset');
                $this->buttonList->remove('save');
                $this->buttonList->remove('delete');

                if (false && $profile->getInvokeStatus() !== 'foreground') {
                    if ($profile->getRunStatus() == 'paused') {
                        $this->buttonList->add('resume', [
                            'label' => __('Resume'),
                            'onclick' => "location.href = '" . $this->getUrl('*/*/resume',
                                                                             ['id' => $id]) . "'",
                        ], 0);
                    } else {
                        $this->buttonList->add('pause', [
                            'label' => __('Pause'),
                            'onclick' => "location.href = '" . $this->getUrl('*/*/pause',
                                                                             ['id' => $id]) . "'",
                        ], 0);
                    }
                }

                $this->buttonList->add('stop', [
                    'label' => __('Stop'),
                    'onclick' => "location.href = '" . $this->getUrl('*/*/stop',
                                                                     ['id' => $id]) . "'",
                    'class' => 'delete',
                ], 0);
                break;

            default:
                $this->buttonList->add('saveandcontinue', [
                    'label' => __('Save And Continue Edit'),
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ],
                    //                    'onclick' => "editForm.submit(\$('#edit_form').attr('action')+'back/edit/')",
                    'class' => 'save',
                ], -100);

                if ($id) {
                    /*
                    $this->buttonList->add(('start_fg', array(
                        'label'     => __('Run Foreground'),
                        'onclick'   => "editForm.submit(\$('edit_form').action+'start/foreground/back/edit/')",
                        'class'     => 'save',
                    ), 0);
                    */
                    $this->buttonList->add('start_bg', [
                        'label' => __('Save And Run'),
                        'data_attribute' => [
                            'mage-init' => [
                                'button' => ['event' => 'saveAndRun', 'target' => '#edit_form'],
                            ],
                        ],
                        //                        'onclick' => "editForm.submit(\$('#edit_form').attr('action')+'start/ondemand/back/edit/')",
                        'class' => 'save',
                    ], 0);
                }
        }
    }

    protected function _prepareLayout()
    {
        $this->addChild('form', 'Unirgy\RapidFlow\Block\Adminhtml\Profile\Edit\Form');
        return parent::_prepareLayout();
    }

    public function getHeaderText()
    {
        $profile = $this->_registry->registry('profile_data');

        if ($profile && $profile->getId()) {
            $title = $this->escapeHtml($profile->getTitle());
            switch ($profile->getRunStatus()) {
                case 'pending':
                case 'running':
                    $title = __("Running Profile State '%1'", $title);
                    break;

                case 'paused':
                    $title = __("Paused Profile State '%1'", $title);
                    break;

                default:
                    $title = __("Edit Profile '%1'", $title);
            }
            return $title;
        } else {
            return __('Add Profile');
        }
    }
}
