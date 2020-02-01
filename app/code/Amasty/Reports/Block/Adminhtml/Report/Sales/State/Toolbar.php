<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Block\Adminhtml\Report\Sales\State;

use Magento\Framework\Data\Form\AbstractForm;

/**
 * Class Toolbar
 * @package Amasty\Reports\Block\Adminhtml\Report\Sales\State
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

        $form->addField('value', 'radios', [
            'name'      => 'value',
            'wrapper_class' => 'amreports-filter-interval amreports-filter-switcher',
            'values'    => [
                ['value' => 'quantity', 'label' => __('Quantity')],
                ['value' => 'total', 'label' => __('Total')]
            ],
            'value'     => 'quantity'
        ]);

        parent::addControls($form);

        $form->addField('country_id', 'select', [
            'name'      => 'country_id',
            'wrapper_class' => 'amreports-select-block amreports-filter-country',
            'class'     => 'amreports-select',
            'values'    => $this->helper->getCountryDataSource()->toOptionArray(),
            'no_span'    => true
        ]);

        return $this;
    }
}
