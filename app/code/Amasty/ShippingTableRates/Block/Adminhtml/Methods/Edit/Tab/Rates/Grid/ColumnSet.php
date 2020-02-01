<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Block\Adminhtml\Methods\Edit\Tab\Rates\Grid;

class ColumnSet extends \Magento\Backend\Block\Widget\Grid\ColumnSet
{
    protected $_objectManager;
    protected $_helper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Backend\Model\Widget\Grid\Row\UrlGeneratorFactory $generatorFactory,
        \Magento\Backend\Model\Widget\Grid\SubTotals $subtotals,
        \Magento\Backend\Model\Widget\Grid\Totals $totals,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Amasty\ShippingTableRates\Helper\Data $helper,
        array $data
    )
    {
        $this->_helper = $helper;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $generatorFactory, $subtotals, $totals, $data);

    }

    protected function _prepareLayout()
    {
        $this->addColumn('country', array(
            'header' => __('Country'),
            'index' => 'country',
            'type' => 'options',
            'options' => $this->_helper->getCountries(),
        ));

        $this->addColumn('state', array(
            'header' => __('State'),
            'index' => 'state',
            'type' => 'options',
            'options' => $this->_helper->getStates(),
        ));

        $this->addColumn('city', array(
            'header' => __('City'),
            'index' => 'city',
            'type' => 'text',
        ));

        $this->addColumn('zip_from', array(
            'header' => __('Zip From'),
            'index' => 'zip_from',
        ));

        $this->addColumn('zip_to', array(
            'header' => __('Zip To'),
            'index' => 'zip_to',
        ));

        $this->addColumn('price_from', array(
            'header' => __('Price From'),
            'index' => 'price_from',
        ));

        $this->addColumn('price_to', array(
            'header' => __('Price To'),
            'index' => 'price_to',
        ));

        $this->addColumn('weight_from', array(
            'header' => __('Weight From'),
            'index' => 'weight_from',
        ));

        $this->addColumn('weight_to', array(
            'header' => __('Weight To'),
            'index' => 'weight_to',
        ));

        $this->addColumn('qty_from', array(
            'header' => __('Qty From'),
            'index' => 'qty_from',
        ));

        $this->addColumn('qty_to', array(
            'header' => __('Qty To'),
            'index' => 'qty_to',
        ));

        $this->addColumn('shipping_type', array(
            'header' => __('Shipping Type'),
            'index' => 'shipping_type',
            'type' => 'options',
            'options' => $this->_helper->getTypes(),
        ));

        $this->addColumn('cost_base', array(
            'header' => __('Rate'),
            'index' => 'cost_base',
        ));

        $this->addColumn('cost_percent', array(
            'header' => __('PPP'),
            'index' => 'cost_percent',
        ));

        $this->addColumn('cost_product', array(
            'header' => __('FRPP'),
            'index' => 'cost_product',
        ));

        $this->addColumn('cost_weight', array(
            'header' => __('FRPUW'),
            'index' => 'cost_weight',
        ));

        $this->addColumn('time_delivery', array(
            'header' => __('Estimated Delivery (days)'),
            'index' => 'time_delivery',
        ));

        $link = $this->getUrl('amstrates/rates/delete') . 'id/$id';
        $this->addColumn('action', array(
            'header' => __('Action'),
            'width' => '50px',
            'type' => 'action',
            'getter' => 'getVid',
            'actions' => [
                [
                    'caption' => __('Delete'),
                    'url' => $link,
                    'field' => 'id',
                    'confirm' => __('Are you sure?')
                ]
            ],
            'filter' => false,
            'sortable' => false,
            'is_system' => true,
        ));
        return parent::_prepareLayout();
    }


    public function addColumn($title, $data)
    {
        $column = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Grid\Column', $title)->addData($data);
        $this->setChild($title, $column);
    }


    public function getRowUrl($item)
    {
        return $this->getUrl('amstrates/rates/edit', ['id' => $item->getId()]);
    }

}
