<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Block\Adminhtml\Report;

use Amasty\Reports\Block\Adminhtml\Navigation;
use Amasty\Reports\Helper\Data;
use Magento\Backend\Block\Template\Context;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Data\Form\AbstractForm;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;

/**
 * Class Toolbar
 * @package Amasty\Reports\Block\Adminhtml\Report
 */
class Toolbar extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var Store
     */
    protected $systemStore;
    /**
     * @var Data
     */
    protected $helper;
    /**
     * @var Collection
     */
    protected $eavCollection;

    /**
     * @var Navigation
     */
    private $navigation;

    /**
     * @var \Amasty\Reports\Model\OptionSource\Rule\FormValue
     */
    private $formValueRules;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var \Amasty\Reports\Block\Adminhtml\Framework\Data\FormFactory
     */
    private $reportFormFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        Data $helper,
        Navigation $navigation,
        Collection $eavCollection,
        DataPersistorInterface $dataPersistor,
        \Amasty\Reports\Model\OptionSource\Rule\FormValue $formValueRules,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Amasty\Reports\Block\Adminhtml\Framework\Data\FormFactory $reportFormFactory,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
        $this->helper = $helper;
        $this->eavCollection = $eavCollection;
        $this->navigation = $navigation;
        $this->dataPersistor = $dataPersistor;
        $this->formValueRules = $formValueRules;
        $this->productMetadata = $productMetadata;
        $this->reportFormFactory = $reportFormFactory;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Amasty\Reports\Block\Adminhtml\Framework\Data\Form $form */
        $form = $this->reportFormFactory->create([
            'data' => [
                'id' => 'report_toolbar',
                'class' => 'amreports-toolbar-container',
                'action' => '',
            ]
        ]);

        $this->addControls($form);

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * @param AbstractForm $parentElement
     *
     * @return $this
     */
    protected function addDateControls(AbstractForm $parentElement)
    {
        $dateFormat = 'y-MM-dd';

        $params = $this->getRequest()->getParam('amreports');

        $parentElement->addField('from', 'date', [
            'label' => __('From'),
            'name' => 'from',
            'wrapper_class' => 'amreports-filter-from',
            'date_format' => $dateFormat,
            'format' => $dateFormat,
            'value' => isset($params['from'])
                ? $params['from']
                : $this->_localeDate->date($this->helper->getDefaultFromDate())
        ]);

        $parentElement->addField('to', 'date', [
            'label' => __('To'),
            'name' => 'to',
            'wrapper_class' => 'amreports-filter-to',
            'format' => $dateFormat,
            'date_format' => $dateFormat,
            'value' =>  isset($params['to'])
                ? $params['to']
                : $this->_localeDate->date($this->helper->getDefaultToDate())
        ]);

        return $this;
    }

    /**
     * @param AbstractForm $form
     *
     * @return $this
     */
    protected function addControls(AbstractForm $form)
    {
        $params = $this->getRequest()->getParam('amreports');

        $form->addField('store', 'select', [
            'name'      => 'store',
            'values'    => $this->systemStore->getStoreValuesForForm(false, true),
            'class'     => 'amreports-select right',
            'wrapper_class' => 'amreports-select-block amreports-select-store',
            'no_span'   => true,
            'value' => isset($params['store']) ? $params['store'] : 0
        ]);

        return $this;
    }

    /**
     * @param AbstractForm $form
     *
     * @return $this
     */
    protected function addViewControls(AbstractForm $form, $values, $defaultValue)
    {
        $params = $this->getRequest()->getParam('amreports');

        $form->addField('view_type', 'radios', [
            'name' => 'view_type',
            'values' => $values,
            'value' => isset($params['view_type']) ? $params['view_type'] : $defaultValue
        ]);

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentTitle()
    {
        $title = $this->navigation->getCurrentTitle();
        return $title;
    }

    /**
     * @param AbstractForm $form
     *
     * @return $this
     */
    protected function addRuleControl(AbstractForm $form)
    {
        $params = $this->getRequest()->getParam('amreports');

        $form->addField('rule', 'select', [
            'label'     => __('Display'),
            'name'      => 'rule',
            'value'     => $params['rule'] ?? '',
            'values'    => $this->formValueRules->toOptionArray(),
            'class'     => 'amreports-select amreports-display-rule',
            'wrapper_class' => 'amreports-select-block amreports-rule-container',
            'no_span'   => true
        ]);

        $form->addField('rule_create', 'link', [
            'value'     => '+ ' . __('New Rule'),
            'class'     => 'amreports-newrule',
            'wrapper_class' => 'amreports-newrule-container',
            'name'      => 'rule_create',
            'no_span'   => true,
            'target'    => '_blank',
            'href'      => $this->_urlBuilder->getUrl('amasty_reports/rule/index')
        ]);

        return $this;
    }

    /**
     * @return string
     */
    public function getDataRole()
    {
        return 'amreports-toolbar';
    }

    /**
     * @return string
     */
    public function getMagentoVersion()
    {
        return $this->productMetadata->getVersion();
    }
}
