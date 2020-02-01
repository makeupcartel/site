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

namespace Unirgy\RapidFlow\Block\Adminhtml\Profile\Edit\Tab\Export;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Config\Model\Config\Source\Website;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form as DataForm;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Unirgy\RapidFlow\Model\Profile;
use Unirgy\RapidFlow\Model\Source;

class Condition extends Generic
{
    /**
     * @var Source
     */
    protected $_rapidFlowSource;

    /**
     * @var Website
     */
    protected $_configSourceWebsite;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Source $rapidFlowSource,
        Website $configSourceWebsite,
        array $data = []
    ) {
        $this->_rapidFlowSource = $rapidFlowSource;
        $this->_configSourceWebsite = $configSourceWebsite;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        /** @var Profile $model */
        $model = $this->_coreRegistry->registry('profile_data');
        $source = $this->_rapidFlowSource;

        //$form = new DataForm(array('id' => 'edit_form1', 'action' => $this->getData('action'), 'method' => 'post'));
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('rule_');

        // conditions
        $renderer = $this->getLayout()->createBlock('\Magento\Backend\Block\Widget\Form\Renderer\Fieldset')
            ->setTemplate(
                'Magento_CatalogRule::promo/fieldset.phtml'
            )->setNewChildUrl(
                $this->getUrl('urapidflow/profile/newConditionHtml', ['form' => 'conditions'])
            );
        $fieldset = $form->addFieldset(
            'conditions_fieldset',
            ['legend' => __('Export only products matching the following conditions (leave blank for all products)')
        ])->setRenderer(
            $renderer
        );

        $fieldset->addField(
            'conditions_post',
            'text',
            [
                'name' => 'conditions_post',
                'label' => __('Export Conditions'),
                'title' => __('Export Conditions'),
                'data-form-part' => 'catalog_rule_form',
                'value' => $model->getConditions()
            ]
        )->setRule(
            $model->getConditionsRule()
        )->setRenderer(
            ObjectManager::getInstance()->get('Magento\Rule\Block\Conditions')
        );

        $fieldset = $form->addFieldset('export_additional_conditions', array('legend' => __('Additional Conditions')));

        $fieldset->addField('export_skip_out_of_stock', 'select', [
            'label' => __('Skip out of stock products'),
            'name' => 'options[export][skip_out_of_stock]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $model->getData('options/export/skip_out_of_stock'),
        ]);

        $fieldset->addField('export_skip_configurable_simples', 'select', [
            'label' => __('Do not export simple products that are used in configurable'),
            'name' => 'options[export][skip_configurable_simples]',
            'values' => $source->setPath('yesno')->toOptionArray(),
            'value' => $model->getData('options/export/skip_configurable_simples'),
        ]);

        $fieldset->addField('export_websites_filter', 'multiselect', [
            'label' => __('Websites filter'),
            'name' => 'options[export][websites_filter]',
            'values' => $this->_configSourceWebsite->toOptionArray(),
            'value' => $model->getData('options/export/websites_filter'),
        ]);

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
