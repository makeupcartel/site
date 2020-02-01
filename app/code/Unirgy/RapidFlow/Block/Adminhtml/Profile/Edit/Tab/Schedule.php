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

class Schedule extends Generic
{

    /**
     * @var Source
     */
    protected $_rapidFlowSource;

    /**
     * @var Registry
     */
    protected $_magentoFrameworkRegistry;

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

        $form = $this->_formFactory->create();
        $this->setForm($form);
        $fieldset = $form->addFieldset('schedule_form', ['legend' => __('Schedule Options')]);

        $fieldset->addField('schedule_enable', 'select', [
            'label' => __('Enable Schedule'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'schedule_enable',
            'values' => $source->setPath('yesno')->toOptionArray(),
        ]);

        $fieldset->addField('schedule_hours', 'multiselect', [
            'label' => __('Hours'),
            'name' => 'schedule_week_days',
            'values' => $source->setPath('schedule_hours')->toOptionArray(),
        ]);

        $fieldset->addField('schedule_week_days', 'multiselect', [
            'label' => __('Week Days'),
            'name' => 'schedule_week_days',
            'values' => $source->setPath('schedule_week_days')->toOptionArray(),
        ]);

        $fieldset->addField('schedule_month_days', 'multiselect', [
            'label' => __('Month Days'),
            'name' => 'schedule_month_days',
            'values' => $source->setPath('schedule_month_days')->toOptionArray(),
        ]);

        $fieldset->addField('schedule_months', 'multiselect', [
            'label' => __('Months'),
            'name' => 'schedule_months',
            'values' => $source->setPath('schedule_months')->toOptionArray(),
        ]);

        if (($profile = $this->_magentoFrameworkRegistry->registry('profile_data'))) {
            $form->setValues($profile->getData());
        }

        return parent::_prepareForm();
    }
}
