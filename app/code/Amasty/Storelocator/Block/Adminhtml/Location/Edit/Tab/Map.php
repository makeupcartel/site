<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Block\Adminhtml\Location\Edit\Tab;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Map extends Generic implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Amasty\Storelocator\Helper\Data
     */
    protected $_helper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Amasty\Storelocator\Helper\Data $helper,
        array $data
    ) {
        $this->_systemStore = $systemStore;
        $this->_helper = $helper;
        parent::__construct($context, $registry, $formFactory, $data);
    }


    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Location On Map');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Location On Map');
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

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_amasty_storelocator_location');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('location_');

        $fieldset = $form->addFieldset('apply_in', ['legend' => __('Apply In')]);

        $fieldset->addField(
            'lat', 'text', array(
                'class'    => 'validate-number',
                'label'    => __('Latitude'),
                'required' => true,
                'name'     => 'lat',
            )
        );

        $fieldset->addField(
            'lng', 'text', array(
                'class'    => 'validate-number',
                'label'    => __('Longitude'),
                'required' => true,
                'name'     => 'lng',
            )
        );

        $fieldset->addField(
            'loadbycoord', 'hidden', array(
                'after_element_html' => '<button id="amlocator_fill" style="" class="scalable" type="button"><span><span><span>Auto Fill</span></span></span></button>'
            )
        );

        $fieldset->addField('marker_img', 'file', array(
            'label'     => __('Custom marker'),
            'name'      => 'marker_img',
            'after_element_html' => $this->getMarkerHtml('marker_img', $model->getMarkerImg()),
        ));

        $fieldset->addField('show_map', 'hidden', array(
                'name'               => 'Show_map',
                'after_element_html' => '<div id="map-canvas" style="margin-top: 20px; width: 515px; height: 515px; display: none"></div>'
            )
        );


        $form->setValues($model->getData());
        $form->addValues(['id'=>$model->getId()]);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    protected function getMarkerHtml($field, $img)
    {
        $html = '';
        if ($img) {
            $html .= '<p style="margin-top: 5px">';
            $html .= '<img id="marker_img" style="max-width:100px" src="' . $this->_helper->getImageUrl($img) . '" />';
            $html .= '<br/><input type="checkbox" value="1" name="remove_' . $field . '"/> ' . __('Remove');
            $html .= '<input type="hidden" value="' . $img . '" name="old_' . $field . '"/>';
            $html .= '</p>';
        }
        return $html;
    }
}