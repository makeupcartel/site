<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2016-05-06T14:53:10+00:00
 * File:          app/code/Xtento/ProductExport/Block/Adminhtml/Profile/Edit/Tab/Automatic.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Block\Adminhtml\Profile\Edit\Tab;

class Automatic extends \Xtento\ProductExport\Block\Adminhtml\Widget\Tab implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $yesNo;

    /**
     * @var \Xtento\XtCore\Helper\Cron
     */
    protected $cronHelper;

    /**
     * @var \Xtento\ProductExport\Model\System\Config\Source\Cron\Frequency
     */
    protected $cronFrequency;

    /**
     * @var \Xtento\ProductExport\Model\System\Config\Source\Export\Events
     */
    protected $exportEvents;

    /**
     * Automatic constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Xtento\XtCore\Helper\Cron $cronHelper
     * @param \Magento\Config\Model\Config\Source\Yesno $yesNo
     * @param \Xtento\ProductExport\Model\System\Config\Source\Cron\Frequency $cronFrequency
     * @param \Xtento\ProductExport\Model\System\Config\Source\Export\Events $exportEvents
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Xtento\XtCore\Helper\Cron $cronHelper,
        \Magento\Config\Model\Config\Source\Yesno $yesNo,
        \Xtento\ProductExport\Model\System\Config\Source\Cron\Frequency $cronFrequency,
        \Xtento\ProductExport\Model\System\Config\Source\Export\Events $exportEvents,
        array $data = []
    ) {
        $this->yesNo = $yesNo;
        $this->cronFrequency = $cronFrequency;
        $this->exportEvents = $exportEvents;
        $this->cronHelper = $cronHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('productexport_profile');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset(
            'cronjob_fieldset',
            [
                'legend' => __('Cronjob Export'),
            ]
        );

        $fieldset->addField(
            'cronjob_note',
            'note',
            [
                'text' => __(
                    '<strong>Important</strong>: To use cron job exports, please make sure the Magento cronjob has been set up as explained <a href="http://support.xtento.com/wiki/Setting_up_the_Magento_cronjob_(Magento_2)" target="_blank">here</a>.'
                )
            ]
        );


        if ($this->cronHelper->isCronRunning()) {
            $model->setCronjobStatus(
                __(
                    "Cron seems to be running properly. Seconds since last execution: %1",
                    (time() - $this->cronHelper->getLastCronExecution())
                )
            );
            $note = '';
        } else {
            if ((time() - $this->cronHelper->getInstallationDate(
                    )) > (60 * 30)
            ) { // Module was not installed within the last 30 minutes
                if ($this->cronHelper->getLastCronExecution() == '') {
                    $model->setCronjobStatus(
                        __("Cron doesn't seem to be set up at all. Cron did not execute within the last 15 minutes.")
                    );
                    $note = __(
                        'Please make sure to set up the cronjob as explained <a href="http://support.xtento.com/wiki/Setting_up_the_Magento_cronjob_(Magento_2)" target="_blank">here</a> and check the cron status 15 minutes after setting up the cronjob properly again.'
                    );
                } else {
                    $model->setCronjobStatus(
                        __(
                            'Cron doesn\'t seem to be set up properly. Cron did not execute within the last 15 minutes. Last execution was %1 seconds ago.',
                            (time() - $this->cronHelper->getLastCronExecution())
                        )
                    );
                    $note = __(
                        'Please make sure to set up the cronjob as explained <a href="http://support.xtento.com/wiki/Setting_up_the_Magento_cronjob_(Magento_2)" target="_blank">here</a> and check the cron status 15 minutes after setting up the cronjob properly again.'
                    );
                }
            } else {
                $model->setCronjobStatus(__("Cron status wasn't checked yet. Please check back in 30 minutes."));
                $note = __(
                    'Please make sure to set up the cronjob as explained <a href="http://support.xtento.com/wiki/Setting_up_the_Magento_cronjob_(Magento_2)" target="_blank">here</a> and check the cron status 15 minutes after setting up the cronjob properly again.'
                );
            }
        }
        $fieldset->addField(
            'cronjob_status',
            'text',
            [
                'label' => __('Cronjob Status'),
                'name' => 'cronjob_status',
                'disabled' => true,
                'note' => $note,
                'value' => $model->getCronjobStatus()
            ]
        );

        $fieldset->addField(
            'cronjob_enabled',
            'select',
            [
                'label' => __('Enable Cronjob Export'),
                'name' => 'cronjob_enabled',
                'values' => $this->yesNo->toOptionArray()
            ]
        );

        $fieldset->addField(
            'cronjob_frequency',
            'select',
            [
                'label' => __('Export Frequency'),
                'name' => 'cronjob_frequency',
                'values' => $this->cronFrequency->toOptionArray(),
                'note' => __('How often should the export be executed?')
            ]
        );

        $fieldset->addField(
            'cronjob_custom_frequency',
            'text',
            [
                'label' => __('Custom Export Frequency'),
                'name' => 'cronjob_custom_frequency',
                'note' => __(
                    'A custom cron expression can be entered here. Make sure to set "Cronjob Frequency" to "Use custom frequency" if you want to enter a custom cron expression here. To set up multiple cronjobs, separate multiple cron expressions by a semi-colon ; Example: */5 * * * *;0 3 * * * '
                ),
                'class' => 'validate-cron',
                'after_element_html' => $this->getCronValidatorJs()
            ]
        );

        $form->setValues($model->getData());

        $exportEvents = $this->exportEvents->toOptionArray($model->getEntity());
        if (count($exportEvents) > 0) {
            $fieldset = $form->addFieldset(
                'event_fieldset',
                [
                    'legend' => __('Event-based Export'),
                    'class' => 'fieldset-wide',
                ]
            );

            $fieldset->addField(
                'event_note',
                'note',
                [
                    'text' => __(
                        'If you want to initiate the export directly after a certain event has been dispatched in Magento, select the appropriate events here. One export per event will be created.'
                    )
                ]
            );

            $fieldset->addField(
                'event_observers',
                'checkboxes',
                [
                    'label' => __('Export Events'),
                    'name' => 'event_observers[]',
                    'values' => $exportEvents,
                    'value' => $model->getEventObservers(),
                    'checked' => $model->getEventObservers(),
                    'after_element_html' => '<small>Please click <a href="http://support.xtento.com/wiki/Magento_2_Extensions:Magento_Product_Export_Module#Event-based_Export" target="_blank">here</a> to learn more about event-based export.</small>',
                ]
            );
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function getCronValidatorJs()
    {
        $errorMsg = __('This is no valid cron expression.');
        $js = <<<EOT
<script>
require(['jquery', 'mage/backend/validation'], function ($) {
    jQuery.validator.addMethod('validate-cron', function(v, e) {
         if (v == "") {
            return true;
         }
         return RegExp("^[-0-9,*/; ]+$","gi").test(v);
    }, '{$errorMsg}');
});
</script>
EOT;

        return $js;
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Automatic Export');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Automatic Export');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}