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

namespace Unirgy\RapidFlowPro\Block\Adminhtml\Profile\Eav;

use Magento\Backend\Block\Widget\Form;
use Magento\Framework\Data\Form as DataForm;
use Unirgy\RapidFlow\Model\Source;
use Unirgy\RapidFlowPro\Block\Adminhtml\Profile\BaseForm;

class ExportOptions
    extends BaseForm
{

    public function _prepareForm()
    {
        $source = $this->_source;

        $profile = $this->_coreRegistry->registry('profile_data');

        $form = $this->_formFactory->create();
        $this->setForm($form);

        $fieldset = $form->addFieldset('import_options_form', ['legend' => __('Import Options')]);

        $fieldset->addField('store_ids', 'multiselect', [
            'label' => __('Stores to Export'),
            'name' => 'options[store_ids]',
            'required' => true,
            'class' => 'required-entry',
            'values' => $source->setPath('stores')->toOptionArray(),
            'value' => $profile->getData('options/store_ids'),
        ]);

        $fieldset->addField('export_row_types', 'multiselect', [
            'label' => __('Row Types'),
            'name' => 'options[row_types]',
            'required' => true,
            'class' => 'required-entry',
            'values' => $source->setDataType($profile->getDataType())
                ->setPath('row_type')->toOptionArray(),
            'value' => $profile->getData('options/row_types'),
        ]);

        $fieldset->addField('export_entity_types', 'multiselect', [
            'label' => __('Entity Types'),
            'name' => 'options[entity_types]',
            'required' => true,
            'class' => 'required-entry',
            'values' => $source->setPath('entity_types')->toOptionArray(),
            'value' => $profile->getData('options/entity_types'),
        ]);

        $fieldset->addField('system_attributes', 'select', [
            'label' => __('Export System Attributes'),
            'name' => 'options[system_attributes]',
            'required' => false,
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $profile->getData('options/export/system_attributes'),
        ]);

        return parent::_prepareForm();
    }
}
