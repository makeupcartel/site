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

use Magento\Framework\Data\Form as DataForm;
use Unirgy\RapidFlowPro\Block\Adminhtml\Profile\BaseForm;

class ImportOptions
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
            'label' => __('Limit Stores to Import'),
            'name' => 'options[store_ids]',
            'values' => $source->setPath('stores')->toOptionArray(),
            'value' => $profile->getData('options/store_ids'),
            'note' => __('wherever applicable'),
        ]);

        $fieldset->addField('import_row_types', 'multiselect', [
            'label' => __('Limit Row Types to Import'),
            'name' => 'options[row_types]',
            'values' => $source->setDataType($profile->getDataType())
                ->setPath('row_type')->toOptionArray(),
            'value' => $profile->getData('options/row_types'),
        ]);

        $fieldset->addField('export_duplicate_option_values', 'select', [
            'label' => __('Allow duplicate option values [EAO]'),
            'name' => 'options[duplicate_option_values]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $profile->getData('options/duplicate_option_values'),
        ]);

        $fieldset->addField('import_reindex_type', 'select', [
            'label' => __('Reindex type'),
            'name' => 'options[import][reindex_type]',
            'values' => $source->setPath('import_reindex_type_nort')->toOptionArray(),
            'value' => $profile->getData('options/import/reindex_type'),
        ]);

        return parent::_prepareForm();
    }
}
