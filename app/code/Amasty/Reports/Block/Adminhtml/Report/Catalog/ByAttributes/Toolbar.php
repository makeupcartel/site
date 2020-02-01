<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Block\Adminhtml\Report\Catalog\ByAttributes;

use Magento\Framework\Data\Form\AbstractForm;

/**
 * Class Toolbar
 * @package Amasty\Reports\Block\Adminhtml\Report\Catalog\ByAttributes
 */
class Toolbar extends \Amasty\Reports\Block\Adminhtml\Report\Toolbar
{
    const ATTRIBUTE_SET = 'attrset';

    /**
     * @param AbstractForm $form
     *
     * @return $this
     */
    protected function addControls(AbstractForm $form)
    {
        $this->addDateControls($form);
        $this->addAttributes($form);

        return parent::addControls($form);
    }

    /**
     * @param $form
     */
    protected function addAttributes($form)
    {
        $this->eavCollection->addFieldToFilter(\Magento\Eav\Model\Entity\Attribute\Set::KEY_ENTITY_TYPE_ID, 4);
        $attrAll = $this->eavCollection->load()->getItems();
        $attributes = [];
        $attributes[] = ['label'=> 'Choose Attribute', 'value' => 'NULL'];
        foreach ($attrAll as $item) {
            if ($item->getFrontendInput() == 'multiselect' || $item->getFrontendInput() == 'select') {
                $attributes[] = ['label' => $item->getFrontendLabel(), 'value' => $item->getAttributeCode()];
            }
        }

        $outputAttributes[] = ['label' => __('Attributes')->render(), 'value' => $attributes];
        $outputAttributes[] = [
            'label' => __('Other')->render(),
            'value' => [['label' => __('Attribute Set')->render(), 'value' => self::ATTRIBUTE_SET]]
        ];

        $form->addField('eav', 'select', [
            'name'      => 'eav',
            'values'    => $outputAttributes,
            'wrapper_class' => 'amreports-select-block',
            'class'     => 'amreports-select',
            'no_span'   => true
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
    }
}
