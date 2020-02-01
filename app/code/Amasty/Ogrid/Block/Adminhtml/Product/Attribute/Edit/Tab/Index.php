<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */

namespace Amasty\Ogrid\Block\Adminhtml\Product\Attribute\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Config\Model\Config\Source\Yesno;

class Index extends \Magento\Eav\Block\Adminhtml\Attribute\Edit\Main\AbstractMain implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $_attributeFactory;

    public function __construct(

        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Eav\Helper\Data $eavData,
        \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory,
        \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory $inputTypeFactory,
        \Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker $propertyLocker,
        \Amasty\Ogrid\Model\AttributeFactory $attributeFactory,
        array $data = []
    ) {
        $this->_attributeFactory = $attributeFactory;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $eavData,
            $yesnoFactory,
            $inputTypeFactory,
            $propertyLocker,
            $data
        );
    }

    /**
     * Tab settings
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Amasty: Extended Order Grid Properties');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Amasty: Extended Order Grid Properties');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $catalogAttributeObject = $this->getAttributeObject();

        $attributeObject = $this->_attributeFactory->create()->load($catalogAttributeObject->getId(), 'attribute_id');

        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $fieldset = $form->addFieldset(
            'amasty_ogrid_index_fieldset',
            ['legend' => __('Amasty: Extended Order Grid Properties'), 'collapsable' => true]
        );

        $yesno = $this->_yesnoFactory->create()->toOptionArray();

        $fieldset->addField(
            'amasty_ogrid_use_in_index',
            'select',
            [
                'name' => 'amasty_ogrid_use_in_index',
                'label' => __('Add to Column Options'),
                'title' => __('Add to Column Options'),
                'note' => __('Select "Yes" to add this attribute to the list of column options in the order grid.'),
                'values' => $yesno,
                'value' => $attributeObject->getId() ? 1 : 0
            ]
        );

        $this->setForm($form);

        return $this;
    }

    public function getAfter()
    {
        return 'front';
    }
}