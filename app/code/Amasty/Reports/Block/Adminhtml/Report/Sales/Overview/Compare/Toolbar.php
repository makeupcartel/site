<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Block\Adminhtml\Report\Sales\Overview\Compare;

use Magento\Framework\Data\Form\AbstractForm;

/**
 * Class Toolbar
 * @package Amasty\Reports\Block\Adminhtml\Report\Sales\Overview\Compare
 */
class Toolbar extends \Amasty\Reports\Block\Adminhtml\Report\Sales\Overview\Toolbar
{
    const FIELDSET_COUNT = 3;
    const COLOR_FIRST_LINE = '#78b5d9';
    const COLOR_SECOND_LINE = '#6f94d7';
    const COLOR_THIRD_LINE = '#7c69d6';

    /**
     * @param AbstractForm $parentElement
     * @return $this|\Amasty\Reports\Block\Adminhtml\Report\Sales\Overview\Toolbar
     */
    protected function addDateControls(AbstractForm $parentElement)
    {
        $dateFormat = 'y-MM-dd';
        $colorsLegend = [];
        if (version_compare($this->getMagentoVersion(), '2.3', '>=')) {
            $colorsLegend = [
                '<span class="range-legend" style="background: ' . self::COLOR_FIRST_LINE . ';" />',
                '<span class="range-legend" style="background: ' . self::COLOR_SECOND_LINE . ';" />',
                '<span class="range-legend" style="background: ' . self::COLOR_THIRD_LINE . ';" />'
            ];
        }

        for ($i = 0; $i < self::FIELDSET_COUNT; $i++) {
            $color = isset($colorsLegend[$i]) ? $colorsLegend[$i] : '';
            $fieldset = $parentElement->addFieldset(
                'from_to_' . $i,
                [
                    'legend' => __('Range #%1   ', $i + 1) . $color,
                    'class' => 'amreports-ranges-fieldset',
                    'wrapperclass' => 'amreports-fieldset-container'
                ]
            );

            list($fromValue, $toValue) = $this->getFromToValues($i);

            $fieldset->addField(
                'from_' . $i,
                'date',
                [
                    'label' => __('From'),
                    'name' => 'from_' . $i,
                    'date_format' => $dateFormat,
                    'format' => $dateFormat,
                    'value' => $fromValue
                ]
            );

            $fieldset->addField(
                'to_' . $i,
                'date',
                [
                    'label' => __('To'),
                    'name' => 'to_' . $i,
                    'format' => $dateFormat,
                    'date_format' => $dateFormat,
                    'value' => $toValue
                ]
            );
        }

        return $this;
    }

    /**
     * @param $index
     * @return array
     */
    private function getFromToValues($index)
    {
        $fromValue = null;
        $toValue = null;
        if ($index == 0) {
            $fromValue = $this->_localeDate->date($this->helper->getDefaultFromDate());
            $toValue = $this->_localeDate->date($this->helper->getDefaultToDate());
        }

        return [$fromValue, $toValue];
    }

    /**
     * @param AbstractForm $form
     * @return $this|\Amasty\Reports\Block\Adminhtml\Report\Sales\Overview\Toolbar
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function addControls(AbstractForm $form)
    {
        parent::addControls($form);
        $form->addField(
            'submit',
            'note',
            [
                'text' => $this->getLayout()->createBlock(
                    \Magento\Backend\Block\Widget\Button::class
                )->setData(
                    ['label' => __('Compare'), 'class' => 'left']
                )->toHtml()
            ]
        );

        return $this;
    }

    /**
     * @return string
     */
    public function getDataRole()
    {
        return 'amreports-toolbar_on_button';
    }

    /**
     * @inheritdoc
     */
    protected function addViewControls(AbstractForm $form, $values, $defaultValue)
    {
        return $this;
    }
}
