<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class AttributesWeight extends AbstractFieldArray
{
    /**
     * @var null
     */
    protected $attributeRenderer = null;

    /**
     * @var null
     */
    protected $weightRenderer = null;

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'attributes_weight',
            [
                'label' => __('Attribute'),
                'renderer' => $this->getAttributeRenderer(),
            ]
        );

        $this->addColumn(
            'weight',
            [
                'label' => __('Weight'),
                'renderer' => $this->getWeightRenderer()
            ]
        );

        $this->_addAfter = false;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAttributeRenderer()
    {
        if (!$this->attributeRenderer) {
            $this->attributeRenderer = $this->getLayout()->createBlock(
                '\Amasty\Xsearch\Block\Adminhtml\Form\Field\Attributes',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->attributeRenderer;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getWeightRenderer()
    {
        if (!$this->weightRenderer) {
            $this->weightRenderer = $this->getLayout()->createBlock(
                '\Amasty\Xsearch\Block\Adminhtml\Form\Field\Weight',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->weightRenderer;
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $options['option_' . $this->getAttributeRenderer()->calcOptionHash($row->getAttributes())]
            = 'selected="selected"';

        $options['option_' . $this->getWeightRenderer()->calcOptionHash($row->getWeight())]
            = 'selected="selected"';

        $row->setData('option_extra_attrs', $options);
    }
}
