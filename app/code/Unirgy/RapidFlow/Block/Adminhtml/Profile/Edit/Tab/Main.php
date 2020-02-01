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
use Unirgy\RapidFlow\Helper\Data as HelperData;
use Unirgy\RapidFlow\Model\Source;

class Main extends Generic
{
    /**
     * @var HelperData
     */
    protected $_rapidFlowHelper;

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

    protected function _prepareForm()
    {
        $source = $this->_rapidFlowSource;

        $profile = $this->_coreRegistry->registry('profile_data');
        $new = !$profile || !$profile->getId();

        $form = $this->_formFactory->create();
        $this->setForm($form);
        $fieldset = $form->addFieldset('profile_form', ['legend' => __('Profile Information')]);

        $fieldset->addField('title', 'text', [
            'label' => __('Title'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'title',
        ]);

        $fieldset->addField('profile_status', 'select', [
            'label' => __('Profile Status'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'profile_status',
            'values' => $source->setPath('profile_status')->toOptionArray(),
        ]);

        if ($new) {
            $fieldset->addField('profile_type', 'select', [
                'label' => __('Profile Type'),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'profile_type',
                'values' => $source->setPath('profile_type')->toOptionArray(),
            ]);

            $fieldset->addField('data_type', 'select', [
                'label' => __('Data Type'),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'data_type',
                'values' => $source->setPath('data_type')->toOptionArray(),
            ]);
        }

        $oldWithDefaultWebsiteFlag = $source->withDefaultWebsite(!$profile || $profile->getDataType() != 'sales');
        $fieldset->addField('store_id', 'select', [
            'label' => __('Store View'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'store_id',
            'values' => $source->setPath('stores')->toOptionArray(),
        ]);
        $source->withDefaultWebsite($oldWithDefaultWebsiteFlag);

        $fieldset->addField('base_dir', 'text', [
            'label' => __('File Location'),
            'name' => 'base_dir',
            'note' => __('Leave empty for default'),
        ]);

        $fieldset->addField('filename', 'text', [
            'label' => __('File Name'),
            'required' => true,
            'class' => 'required-entry',
            'name' => 'filename',
        ]);

        if (!$new) {
            $fieldset->addField('profile_type', 'select', [
                'label' => __('Profile Type'),
                'disabled' => true,
                'name' => 'profile_type',
                'values' => $source->setPath('profile_type')->toOptionArray(),
            ]);

            $fieldset->addField('data_type', 'select', [
                'label' => __('Data Type'),
                'disabled' => true,
                'name' => 'data_type',
                'values' => $source->setPath('data_type')->toOptionArray(),
            ]);

            $fieldset->addField('run_status', 'select', [
                'label' => __('Run Status'),
                'disabled' => true,
                'name' => 'run_status',
                'values' => $source->setPath('run_status')->toOptionArray(),
            ]);
            $fieldset->addField('invoke_status', 'select', [
                'label' => __('Invoke Status'),
                'disabled' => true,
                'name' => 'invoke_status',
                'values' => $source->setPath('invoke_status')->toOptionArray(),
            ]);
        }

        if ($profile) {
            $form->setValues($profile->getData());
        }

        $fieldset = $form->addFieldset('log_form', ['legend' => __('Logging Options')]);

        $fieldset->addField('minimum_log_level', 'select', [
            'label' => __('Minimum Log Level'),
            'name' => 'options[log][min_level]',
            'values' => $source->setPath('log_level')->toOptionArray(),
            'value' => $profile->getData('options/log/min_level'),
        ]);

        $fieldset->addField('debug', 'select', [
            'label' => __('Log Debugging Information'),
            'name' => 'options[debug]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $profile->getData('options/debug'),
            'note' => 'This will potentially increase the size of plain log a lot.'
        ]);

        if (!$new && in_array($profile->getDataType(), ['category', 'product_extra'])) {
            $fieldset = $form->addFieldset('category_specific_form', ['legend' => __('Category Options')]);
            $fieldset->addField($profile->getProfileType() . '_urlpath_prepend_root', 'select', [
                'label' => __(
                    $profile->getProfileType() == 'export'
                        ? 'Prepend Root Category Name To URL Paths'
                        : 'Use Prepended Root Category Name To URL Paths'
                ),
                'name' => 'options[' . $profile->getProfileType() . '][urlpath_prepend_root]',
                'values' => $source->setPath('yesno')->toOptionArray(),
                'value' => $profile->getData('options/' . $profile->getProfileType() . '/urlpath_prepend_root'),
                'note' => __(
                    'Serves as a workaround when there are multiple root categories with identical trees, or tree elements. <br>'
                    . 'Hence subcategories within different root categories have identical url paths.'
                ),
            ]);
        }

        return parent::_prepareForm();
    }
}
