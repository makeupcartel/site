<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Block\Adminhtml\Report\Sales\Orders;

use Magento\Framework\Data\Form\AbstractForm;

/**
 * Class Toolbar
 * @package Amasty\Reports\Block\Adminhtml\Report\Sales\Orders
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

        $form->addField('type', 'select', [
            'name'      => 'type',
            'wrapper_class' => 'amreports-select-block',
            'values'    => [
                ['value' => 'overview', 'label' => __('Overview')],
                ['value' => 'status', 'label' => __('By Status')]
            ],
            'value'     => 'type',
            'class'     => 'amreports-select',
            'no_span'    => true
        ]);

        $form->addField('value', 'radios', [
            'name'      => 'value',
            'wrapper_class' => 'amreports-filter-interval amreports-filter-switcher',
            'values'    => [
                ['value' => 'quantity', 'label' => __('Quantity')],
                ['value' => 'total', 'label' => __('Total')]
            ],
            'value'     => 'quantity'
        ]);

        $this->addViewControls(
            $form,
            [
                ['value' => 'line', 'label' => __('Line')],
                ['value' => 'column', 'label' => __('Columns')]
            ],
            'column'
        );
        
        return parent::addControls($form);
    }
}
