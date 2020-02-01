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

namespace Unirgy\RapidFlowPro\Block\Adminhtml\Profile\Category;

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

        $fieldset->addField('import_actions', 'select', [
            'label' => __('Allowed Import Actions'),
            'name' => 'options[import][actions]',
            'values' => $source->setPath('import_actions')->toOptionArray(),
            'value' => $profile->getData('options/import/actions'),
        ]);

        $fieldset->addField('import_dryrun', 'select', [
            'label' => __('Dry Run (validate data only)'),
            'name' => 'options[import][dryrun]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $profile->getData('options/import/dryrun'),
        ]);

        $fieldset->addField('import_select_ids', 'select', [
            'label' => __('Allow internal values for dropdown attributes'),
            'name' => 'options[import][select_ids]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $profile->getData('options/import/select_ids'),
        ]);

        $fieldset->addField('import_same_as_default', 'select', [
            'label' => __('If store values the same as default'),
            'name' => 'options[import][store_value_same_as_default]',
            'values' => $source->setPath('store_value_same_as_default')->toOptionArray(),
            'value' => $profile->getData('options/import/store_value_same_as_default'),
            'comment' => __('Affects only updated values'),
        ]);

        $fieldset->addField('import_force_urlrewrite_refresh', 'select', [
            'label' => __('Force URL Rewrites Refresh'),
            'name' => 'options[import][force_urlrewrite_refresh]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $profile->getData('options/import/force_urlrewrite_refresh'),
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
