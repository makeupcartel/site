<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Block\Adminhtml\Report\Catalog\ProductPerformance;

use Magento\Framework\Data\Form\AbstractForm;

/**
 * Class Toolbar
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
        $this->addSkuControl($form);

        return parent::addControls($form);
    }

    /**
     * @param AbstractForm $form
     */
    private function addSkuControl($form)
    {
        $form->addField(
            'sku',
            'text',
            [
                'name' => 'sku',
                'placeholder' => __('Product SKU'),
                'required' => true,
                'class' => 'amreport-sku-field',
            ]
        );
        $form->addField(
            'submit',
            'note',
            [
                'text' => $this->getLayout()->createBlock(
                    \Magento\Backend\Block\Widget\Button::class
                )->setData(
                    ['label' => __('Show Report'), 'class' => 'left']
                )->toHtml()
            ]
        );
    }

    /**
     * @return string
     */
    public function getDataRole()
    {
        return 'amreports-toolbar_on_button';
    }
}
