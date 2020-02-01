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

class Export extends Generic
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

        $fieldset = $form->addFieldset('export_options_form', ['legend' => __('Export Options')]);

        $fieldset->addField('export_image_files', 'select', [
            'label' => __('Auto-export image files'),
            'name' => 'options[export][image_files]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $profile->getData('options/export/image_files'),
        ]);
        $fieldset->addField('export_image_https', 'select', [
            'label' => __('Export Image URLs as HTTPS'),
            'name' => 'options[export][image_https]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $profile->getData('options/export/image_https'),
        ]);

        $fieldset->addField('export_image_retain_folders', 'select', [
            'label' => __('Export Image Retain Folder Structure'),
            'name' => 'options[export][image_retain_folders]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $profile->getData('options/export/image_retain_folders'),
            'note' => __("When exporting images keep folder structure as stored in database or export flat folder with images.")
        ]);
        $fieldset->addField('export_invalid_values', 'select', [
            'label' => __('Export invalid values'),
            'name' => 'options[export][invalid_values]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $profile->getData('options/export/invalid_values'),
        ]);
        $fieldset->addField('export_internal_values', 'select', [
            'label' => __('Export internal values'),
            'name' => 'options[export][internal_values]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $profile->getData('options/export/internal_values'),
        ]);

        $fieldset->addField('export_configurable_qty_as_sum', 'select', [
            'label' => __('Calculate qty of configurable products as sum of subproducts'),
            'name' => 'options[export][configurable_qty_as_sum]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $profile->getData('options/export/configurable_qty_as_sum'),
        ]);

        $fieldset = $this->getForm()->addFieldset('export_price_form', ['legend' => __('Price Options')]);

        /*
        $fieldset->addField('export_use_final_price', 'select', array(
            'label'     => __('Use Final Price'),
            'name'      => 'options[export][use_final_price]',
            'values'    => $source->setPath('yesno')->toOptionArray(),
            'value'     => $profile->getData('options/export/use_final_price'),
        ));
        $fieldset->addField('export_use_minimal_price', 'select', array(
            'label'     => __('Use Minimal Price'),
            'name'      => 'options[export][use_minimal_price]',
            'values'    => $source->setPath('yesno')->toOptionArray(),
            'value'     => $profile->getData('options/export/use_minimal_price'),
        ));
        */
        $fieldset->addField('export_add_tax', 'select', [
            'label' => __('Add Tax'),
            'name' => 'options[export][add_tax]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $profile->getData('options/export/add_tax'),
        ]);
        $fieldset->addField('export_markup', 'text', [
            'label' => __('Add Markup (%)'),
            'name' => 'options[export][markup]',
            'value' => $profile->getData('options/export/markup'),
        ]);

        $fieldset->addField('export_load_product', 'select', [
            'label' => __('Load Product'),
            'name' => 'options[export][load_product]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $profile->getData('options/export/load_product'),
            'note' => __("If Price Index Is Empty some prices can be calculated, but product needs to be loaded.<br/>Loading products will slow down overall profile execution.")
        ]);

        return parent::_prepareForm();
    }
}
