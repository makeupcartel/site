<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Block\Adminhtml\Report\Sales\Category;

use Magento\Framework\Data\Form\AbstractForm;

/**
 * Class Toolbar
 * @package Amasty\Reports\Block\Adminhtml\Report\Sales\Category
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

        $this->addViewControls(
            $form,
            [
                ['value' => 'pie', 'label' => __('Pie')],
                ['value' => 'horizontal-column', 'label' => __('Horizontal-columns')]
            ],
            'pie'
        );
        
        return parent::addControls($form);
    }
}
