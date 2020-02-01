<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Block\Adminhtml\Location\Edit\Tab;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Schedule extends Generic implements TabInterface
{

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_store;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    protected $_wysiwygConfig;

    /**
     * @var \Amasty\Storelocator\Helper\Data
     */
    protected $_helper;

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Store Schedule');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Store Schedule');
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
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $store
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Amasty\Storelocator\Model\WysiwygConfig $wysiwygConfig,
        \Magento\Store\Model\System\Store $store,
        \Amasty\Storelocator\Helper\Data $helper,
        array $data = []
    ) {
        $this->_store = $store;
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_helper = $helper;
        $this->_storeManager = $context->getStoreManager();
        parent::__construct($context, $registry, $formFactory, $data);
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

        //add day schedule from Monday to Sunday
        foreach ($this->_helper->getDaysNames() as $key => $day) {
            $this->addDaySchedule($form, $key, $day->getText());
        }

        $form->addValues(['id'=>$model->getId()]);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    public function addDaySchedule($form, $dayNum, $dayName)
    {
        $fieldset = $form->addFieldset(
            $dayNum,
            [
                'legend' => __(ucfirst($dayName).' Schedule'),
                'class'     => 'fieldset-wide',
                'expanded'  => true,
            ]
        );
        $fieldset->addType(
            'amastyTime',
            '\Amasty\Storelocator\Block\Adminhtml\Location\Edit\Renderer\Time'
        );
        $fieldset->addField(
            'schedule['.$dayNum.'][from]', 'amastyTime', [
                'label'    => __('Open Time'),
                'required' => true,
                'name'     => 'schedule['.$dayNum.'][from]',
                'value'    => $this->getScheduleValue($dayNum, 'from'),
            ]
        );
        $fieldset->addField(
            'schedule['.$dayNum.'][to]', 'amastyTime', [
                'label'    => __('Close Time'),
                'required' => true,
                'name'     => 'schedule['.$dayNum.'][to]',
                'value'    => $this->getScheduleValue($dayNum, 'to'),
            ]
        );
    }

    public function getScheduleValue($day, $time)
    {
        $model = $this->_coreRegistry->registry('current_amasty_storelocator_location');
        $data = $model->getData('schedule');
        $dayNumberForSchedule = date("N", strtotime($day));
        if (isset($data[$day][$time])) {
            return $data[$day][$time];
        } elseif (isset($data[$dayNumberForSchedule][$time])) {
            // return weekday information if it is stored in the database
            return $data[$dayNumberForSchedule][$time];
        }
    }
}
