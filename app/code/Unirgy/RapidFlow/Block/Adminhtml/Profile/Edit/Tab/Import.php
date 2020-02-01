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
use Magento\Framework\View\LayoutFactory;
use Unirgy\RapidFlow\Helper\Data as HelperData;
use Unirgy\RapidFlow\Model\Source;

class Import extends Generic
{
    /**
     * @var HelperData
     */
    protected $_rapidFlowHelper;

    /**
     * @var Source
     */
    protected $_rapidFlowSource;

    /**
     * @var Registry
     */
    protected $_magentoFrameworkRegistry;
    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    public function __construct(Context $context,
                                Registry $registry,
                                FormFactory $formFactory,
                                HelperData $rapidFlowHelper,
                                Source $rapidFlowSource,
                                LayoutFactory $layoutFactory,
                                array $data = []
    )
    {
        $this->_rapidFlowHelper = $rapidFlowHelper;
        $this->_rapidFlowSource = $rapidFlowSource;
        $this->layoutFactory = $layoutFactory;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function _prepareForm()
    {
        $source = $this->_rapidFlowSource;

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

        $fieldset->addField('import_change_typeset', 'select', [
            'label' => __('Allow changing product type and attribute set for existing products'),
            'name' => 'options[import][change_typeset]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $profile->getData('options/import/change_typeset'),
        ]);

        $fieldset->addField('import_select_ids', 'select', [
            'label' => __('Allow internal values for dropdown attributes'),
            'name' => 'options[import][select_ids]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $profile->getData('options/import/select_ids'),
        ]);

        $fieldset->addField('import_not_applicable', 'select', [
            'label' => __('Allow importing values for not applicable attributes'),
            'name' => 'options[import][not_applicable]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $profile->getData('options/import/not_applicable'),
        ]);

        $fieldset->addField('import_same_as_default', 'select', [
            'label' => __('If store values the same as global values'),
            'name' => 'options[import][store_value_same_as_default]',
            'values' => $source->setPath('store_value_same_as_default')->toOptionArray(),
            'value' => $profile->getData('options/import/store_value_same_as_default'),
            'comment' => __('Affects only updated values'),
        ]);

        $fieldset->addField('import_empty_value_strategy', 'select', [
            'label' => __('Empty values strategy'),
            'name' => 'options[import][empty_value_strategy]',
            'values' => $source->setPath('empty_value_strategy')->toOptionArray(),
            'value' => $profile->getData('options/import/empty_value_strategy'),
        ]);

        $fieldset->addField('import_stock_zero_out', 'select', [
            'label' => __('If stock qty is less than configured minimum qty, mark product as Out of stock'),
            'name' => 'options[import][stock_zero_out]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $profile->getData('options/import/stock_zero_out'),
        ]);

        $fieldset->addField('import_force_urlrewrite_refresh', 'select', [
            'label' => __('Force URL Rewrites Refresh'),
            'name' => 'options[import][force_urlrewrite_refresh]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $profile->getData('options/import/force_urlrewrite_refresh'),
        ]);

        $enableUrlKeyIncrementField = $fieldset->addField('increment_url_key', 'select', [
            'label' => __('Try to auto increment duplicate url_key'),
            'name' => 'options[import][increment_url_key]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $profile->getData('options/import/increment_url_key'),
        ]);

        // add dependency fields for each sales type option to select import/export columns
        $el = $fieldset->addField('increment_url_key_limit', 'text', [
            'label' => __('Key suffix limit'),
            'name' => 'options[import][increment_url_key_limit]',
            'value' => $profile->getData('options/import/increment_url_key_limit'),
            'note' => __('If empty, 100 will be used.'),
        ]);

        $enableUrlKeyIncrementFieldName = $enableUrlKeyIncrementField->getName();
        /** @var \Magento\Backend\Block\Widget\Form\Element\Dependence $dependenceBlock */
        $dependenceBlock          = $this->layoutFactory->create()->createBlock(Form\Element\Dependence::class);

        $dependenceBlock->addFieldMap($enableUrlKeyIncrementField->getHtmlId(), $enableUrlKeyIncrementFieldName);

        $dependenceBlock->addFieldMap($el->getHtmlId(), $el->getName())
            ->addFieldDependence($el->getName(),
                $enableUrlKeyIncrementFieldName,
                '1');

        $this->addChild('form_after', $dependenceBlock);

        $fieldset->addField('import_reindex_type', 'select', [
            'label' => __('Reindex type'),
            'name' => 'options[import][reindex_type]',
            'values' => $source->setPath('import_reindex_type')->toOptionArray(),
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

        $fieldset->addField('import_image_files_remote_batch', 'select', [
            'label' => __('Batch downloading remote HTTP images'),
            'name' => 'options[import][image_files_remote_batch]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $profile->getData('options/import/image_files_remote_batch'),
        ]);

        $fieldset->addField('import_image_remote_subfolder_level', 'select', [
            'label' => __('Retain remote subfolders'),
            'name' => 'options[import][image_remote_subfolder_level]',
            'values' => $source->setPath('import_image_remote_subfolder_level')->toOptionArray(),
            'value' => $profile->getData('options/import/image_remote_subfolder_level'),
        ]);

        $fieldset->addField('import_image_delete_old', 'select', [
            'label' => __('Delete old image'),
            'name' => 'options[import][image_delete_old]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $profile->getData('options/import/image_delete_old'),
            'note' => __('Old image will be deleted from filesystem only if not used by other products or "Skip usage check when delete" = "Yes""'),
        ]);

        $fieldset->addField('import_image_delete_skip_usage_check', 'select', [
            'label' => __('Skip usage check when delete old image'),
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

        $fieldset = $form->addFieldset('import_autocreate_options_form',
                                       ['legend' => __('Auto-Create Missing Attribute Option Values')]);

        $fieldset->addField('import_create_options', 'select', [
            'label' => __('Enable'),
            'name' => 'options[import][create_options]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $profile->getData('options/import/create_options'),
        ]);

        $fieldset = $form->addFieldset('import_autocreate_categories_form',
                                       ['legend' => __('Auto-Create Categories (category.name column only)')]);

        $fieldset->addField('import_create_categories', 'select', [
            'label' => __('Enable'),
            'name' => 'options[import][create_categories]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $profile->getData('options/import/create_categories'),
        ]);

        $fieldset->addField('import_create_categories_active', 'select', [
            'label' => __('Default Active?'),
            'name' => 'options[import][create_categories_active]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $profile->getData('options/import/create_categories_active'),
        ]);

        $fieldset->addField('import_create_categories_anchor', 'select', [
            'label' => __('Default Anchored?'),
            'name' => 'options[import][create_categories_anchor]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $profile->getData('options/import/create_categories_anchor'),
        ]);

        $fieldset->addField('import_create_categories_display', 'select', [
            'label' => __('Default Display Mode'),
            'name' => 'options[import][create_categories_display]',
            'values' => $source->setPath('category_display_mode')->toOptionArray(),
            'value' => $profile->getData('options/import/create_categories_display'),
        ]);


        $fieldset->addField('import_create_categories_menu', 'select', [
            'label' => __('Default Include In Menu?'),
            'name' => 'options[import][create_categories_menu]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $profile->getData('options/import/create_categories_menu'),
        ]);

        $fieldset->addField('import_delete_old_category_products', 'select', [
            'label' => __('Delete old category-product associations'),
            'name' => 'options[import][delete_old_category_products]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $profile->getData('options/import/delete_old_category_products'),
        ]);

        $fieldset = $form->addFieldset('import_autocreate_attributeset_form',
                                       array('legend' => __('Auto-Create Attribute Sets')));

        $fieldset->addField('import_create_attributesets', 'select', [
            'label' => __('Enable'),
            'name' => 'options[import][create_attributesets]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $profile->getData('options/import/create_attributesets'),
        ]);


        $fieldset->addField('import_create_attributeset_template', 'select', [
            'label' => __('Auto-created Attribute Set Template'),
            'name' => 'options[import][create_attributeset_template]',
            'values' => $source->setPath('attribute_sets')->toOptionArray(),
            'value' => $profile->getData('options/import/create_attributeset_template'),
        ]);

        $fieldset = $form->addFieldset('import_advanced_form', ['legend' => __('Advanced Settings')]);

        /*
                $fieldset->addField('import_save_attributes_method', 'select', array(
                    'label'     => __('Save Attributes Method'),
                    'name'      => 'options[import][save_attributes_method]',
                    'values'    => $source->setPath('save_attributes_method')->toOptionArray(),
                    'value'     => $profile->getData('options/import/save_attributes_method'),
                    'note'   => __('Use PDOStatement for long text attributes (>10KB)<br/>PDOStatement method might not work with some PHP versions (5.2.6)'),
                ));
        */

        $fieldset->addField('import_insert_attr_chunk_size', 'text', [
            'label' => __('Insert Attribute Values Chunk Size'),
            'name' => 'options[import][insert_attr_chunk_size]',
            'value' => $profile->getData('options/import/insert_attr_chunk_size'),
            'note' => __('Number of attribute value records to insert at the same time.Default value is 100. If there are large text values, use small number (1), but it might affect inserting new attribute values performance.'),
        ]);

        return parent::_prepareForm();
    }
}
