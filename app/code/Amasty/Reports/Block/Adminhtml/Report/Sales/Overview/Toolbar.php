<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Block\Adminhtml\Report\Sales\Overview;

use Magento\Framework\Data\Form\AbstractForm;

/**
 * Class Toolbar
 * @package Amasty\Reports\Block\Adminhtml\Report\Sales\Overview
 */
class Toolbar extends \Amasty\Reports\Block\Adminhtml\Report\Toolbar
{
    /**
     * @param AbstractForm $form
     *
     * @return $this
     */
    protected function addControls(AbstractForm $form)
    {
        $this->addDateControls($form);

        $form->addField(
            'interval',
            'radios',
            [
                'name' => 'interval',
                'wrapper_class' => 'amreports-filter-interval',
                'values' => [
                    ['value' => 'day', 'label' => __('Day')],
                    ['value' => 'week', 'label' => __('Week')],
                    ['value' => 'month', 'label' => __('Month')],
                    ['value' => 'year', 'label' => __('Year')],
                ],
                'value' => 'day'
            ]
        );

        $this->addViewControls(
            $form,
            [
                ['value' => 'line', 'label' => __('Line')],
                ['value' => 'column', 'label' => __('Columns')]
            ],
            'line'
        );

        parent::addControls($form);

        return $this;
    }
}
