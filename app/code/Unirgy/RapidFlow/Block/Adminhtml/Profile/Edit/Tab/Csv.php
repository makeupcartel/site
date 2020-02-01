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
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\Form as DataForm;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Unirgy\RapidFlow\Model\Source;

class Csv extends Generic
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
        $fieldset = $form->addFieldset('csv_form', ['legend' => __('CSV Options')]);

        $encodings = $source->setPath('encoding')->toOptionArray();
        if ($profile->getProfileType() == 'import') {
            $fieldset->addField('encoding_from', 'select', [
                'label' => __('File Encoding'),
                'name' => 'options[encoding][from]',
                'value' => $profile->getData('options/encoding/from'),
                'values' => $encodings,
            ]);
        } else {
            unset($encodings['auto']);
            $fieldset->addField('encoding_to', 'select', [
                'label' => __('File Encoding'),
                'name' => 'options[encoding][to]',
                'value' => $profile->getData('options/encoding/to'),
                'values' => $encodings,
            ]);
        }

        $fieldset->addField('encoding_illegal_char', 'select', [
            'label' => __('Action to take on illegal character during conversion'),
            'name' => 'options[encoding][illegal_char]',
            'values' => $source->setPath('encoding_illegal_char')->toOptionArray(),
            'value' => $profile->getData('options/encoding/illegal_char'),
        ]);

        $fieldset->addField('csv_delimiter', 'text', [
            'label' => __('Field Delimiter'),
            'required' => true,
            'class' => 'required-entry',
            'name' => 'options[csv][delimiter]',
            'value' => $profile->getData('options/csv/delimiter'),
        ]);

        $fieldset->addField('csv_enclosure', 'text', [
            'label' => __('Field Enclosure'),
            'required' => true,
            'class' => 'required-entry',
            'name' => 'options[csv][enclosure]',
            'value' => $profile->getData('options/csv/enclosure'),
        ]);
        /*
                $fieldset->addField('csv_escape', 'text', array(
                    'label'     => __('Quote Escape'),
                    'required'  => true,
                    'class'     => 'required-entry',
                    'name'      => 'options[csv][escape]',
                    'value'     => $profile->getData('options/csv/escape'),
                ));
        */
        $fieldset->addField('csv_multivalue_separator', 'text', [
            'label' => __('Default Multivalue Separator'),
            'required' => true,
            'class' => 'required-entry',
            'name' => 'options[csv][multivalue_separator]',
            'value' => $profile->getData('options/csv/multivalue_separator'),
        ]);

        return parent::_prepareForm();
    }
}
