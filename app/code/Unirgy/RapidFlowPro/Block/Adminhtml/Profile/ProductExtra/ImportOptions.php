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

namespace Unirgy\RapidFlowPro\Block\Adminhtml\Profile\ProductExtra;

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
            'values' => $source->setDataType($profile->getDataType())->setStripFromLabel('/^Catalog Product/')
                ->setPath('row_type')->toOptionArray(),
            'value' => $profile->getData('options/row_types'),
        ]);

        $fieldset->addField('import_reindex_type', 'select', [
            'label' => __('Reindex type'),
            'name' => 'options[import][reindex_type]',
            'values' => $source->setPath('import_reindex_type_nort')->toOptionArray(),
            'value' => $profile->getData('options/import/reindex_type'),
        ]);

        $fieldset = $form->addFieldset('import_images', ['legend' => __('Image Options')]);

        $fieldset->addField('import_image_files', 'select', [
            'label' => __('Auto-import image files'),
            'name' => 'options[import][image_files]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $profile->getData('options/import/image_files'),
        ]);

        $fieldset->addField('import_image_files_remote', 'select', [
            'label' => __('Download remote HTTP images'),
            'name' => 'options[import][image_files_remote]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $profile->getData('options/import/image_files_remote'),
            'note' => __('Might not work for dynamically generated remote images'),
        ]);

        $fieldset->addField('import_image_remote_subfolder_level', 'select', [
            'label' => __('Retain remote subfolders'),
            'name' => 'options[import][image_remote_subfolder_level]',
            'values' => $source->setPath('import_image_remote_subfolder_level')->toOptionArray(),
            'value' => $profile->getData('options/import/image_remote_subfolder_level'),
        ]);

        $fieldset->addField('import_image_delete_skip_usage_check', 'select', [
            'label' => __('Skip usage check when delete image'),
            'name' => 'options[import][image_delete_skip_usage_check]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $profile->getData('options/import/image_delete_skip_usage_check'),
            'note' => __('Setting this option will skip check for usage of image to delete by other products'),
        ]);

        $fieldset->addField('import_image_missing_file', 'select', [
            'label' => __('Action on missing image file'),
            'name' => 'options[import][image_missing_file]',
            'values' => $source->setPath('import_image_missing_file')->toOptionArray(),
            'value' => $profile->getData('options/import/image_missing_file'),
        ]);

        $fieldset->addField('import_image_existing_file', 'select', [
            'label' => __('Action on existing image file'),
            'name' => 'options[import][image_existing_file]',
            'values' => $source->setPath('import_image_existing_file')->toOptionArray(),
            'value' => $profile->getData('options/import/image_existing_file'),
            'note' => __('Select what to do when imported image has same name as existing image.'),
        ]);

        $fieldset->addField('import_image_source_dir', 'text', [
            'label' => __('Local Source Folder'),
            'name' => 'options[dir][images]',
            'value' => $profile->getData('options/dir/images'),
            'note' => __('If empty, global configuration will be used'),
        ]);
        /*
        $fieldset->addField('import_delete_image_files', 'select', array(
            'label'     => __('Auto-delete image files'),
            'name'      => 'options[import][delete_image_files]',
            'values'    => $source->setPath('yesno')->toOptionArray(),
            'value'     => $profile->getData('options/import/delete_image_files'),
        ));
        */

        return parent::_prepareForm();
    }
}
