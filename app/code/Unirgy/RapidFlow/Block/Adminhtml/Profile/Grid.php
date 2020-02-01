<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    \Unirgy\RapidFlow
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\RapidFlow\Block\Adminhtml\Profile;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended as WidgetGrid;
use Magento\Backend\Helper\Data as HelperData;
use Unirgy\RapidFlow\Helper\Data as RapidFlowHelperData;
use Unirgy\RapidFlow\Model\Profile;
use Unirgy\RapidFlow\Model\Source;

class Grid extends WidgetGrid
{
    /**
     * @var Profile
     */
    protected $_profile;

    /**
     * @var RapidFlowHelperData
     */
    protected $_rapidFlowHelper;

    /**
     * @var Source
     */
    protected $_rapidFlowSource;

    public function __construct(
        Context $context,
        HelperData $backendHelper,
        Profile $rapidFlowModelProfile,
        RapidFlowHelperData $rapidFlowHelperData,
        Source $source,
        array $data = []
    ) {
        $this->_profile = $rapidFlowModelProfile;
        $this->_rapidFlowHelper = $rapidFlowHelperData;
        $this->_rapidFlowSource = $source;

        parent::__construct($context, $backendHelper, $data);
        $this->setId('profileGrid');
        $this->setDefaultSort('profile_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInProfile(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->_profile->getCollection();
        /*
        $collection->getSelect()->columns(array(
            'rows_status'=>new Expr("concat(rows_processed,' / ',rows_errors)", 'main_table')
        ));
        */
        $this->setCollection($collection);
        if ($this->_rapidFlowHelper->hasEeGwsFilter()) {
            $collection->addFieldToFilter('store_id', array('in' => $this->_rapidFlowHelper->getEeGwsStoreIds()));
        }
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $hlp = $this->_rapidFlowHelper;
        $source = $this->_rapidFlowSource;

        $this->addColumn('profile_id', [
            'header' => __('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'profile_id',
            'type' => 'number',
        ]);

        $this->addColumn('title', [
            'header' => __('Title'),
            'align' => 'left',
            'index' => 'title',
        ]);

        /*
        $this->addColumn('content', array(
            'header'    => __('Item Content'),
            'width'     => '150px',
            'index'     => 'content',
        ));
        */

        $this->addColumn('started_at', [
            'header' => __('Last Run'),
            'align' => 'left',
            'width' => '130px',
            'index' => 'started_at',
            'type' => 'datetime',
        ]);

        $this->addColumn('rows_processed', [
            'header' => __('Rows'),
            'align' => 'left',
            'width' => '60px',
            'filter' => false,
            'index' => 'rows_processed',
        ]);

        $this->addColumn('rows_errors', [
            'header' => __('Errors'),
            'align' => 'left',
            'width' => '60px',
            'filter' => false,
            'index' => 'rows_errors',
        ]);
        /*
                $this->addColumn('scheduled_at', array(
                    'header'    => __('Next Schedule'),
                    'align'     => 'left',
                    'width'     => '130px',
                    'index'     => 'scheduled_at',
                    'type'      => 'datetime',
                ));
        */
        $this->addColumn('profile_status', [
            'header' => __('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'profile_status',
            'type' => 'options',
            'options' => $source->setPath('profile_status')->toOptionHash(),
            'renderer' => 'Unirgy\RapidFlow\Block\Adminhtml\Profile\Grid\Status',
        ]);

        $this->addColumn('run_status', [
            'header' => __('Activity'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'run_status',
            'type' => 'options',
            'options' => $source->setPath('run_status')->toOptionHash(),
            'renderer' => 'Unirgy\RapidFlow\Block\Adminhtml\Profile\Grid\Status',
        ]);
        /*
                $this->addColumn('invoke_status', array(
                    'header'    => __('Invoke Status'),
                    'align'     => 'left',
                    'width'     => '80px',
                    'index'     => 'invoke_status',
                    'type'      => 'options',
                    'options'   => $source->setPath('invoke_status')->toOptionHash(),
                    'renderer'  => 'Unirgy\RapidFlow\Block\Adminhtml\Profile\Grid\Status',
                ));
        */
        $this->addColumn('data_type', [
            'header' => __('Data Type'),
            'align' => 'left',
            'index' => 'data_type',
            'type' => 'options',
            'options' => $source->setPath('data_type')->toOptionHash(),
        ]);

        $this->addColumn('profile_type', [
            'header' => __('Profile Type'),
            'align' => 'left',
            'index' => 'profile_type',
            'type' => 'options',
            'options' => $source->setPath('profile_type')->toOptionHash(),
        ]);

        /*
                $this->addColumn('action', array(
                    'header'    =>  __('Action'),
                    'width'     => '100',
                    'type'      => 'action',
                    'getter'    => 'getId',
                    'actions'   => array(
                        array(
                            'caption'   => __('Edit'),
                            'url'       => array('base'=> '* /* /edit'),
                            'field'     => 'id'
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,
                ));
        */
        //$this->addExportType('*/*/exportCsv', __('CSV'));
        //$this->addExportType('*/*/exportXml', __('XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        if ($this->_scopeConfig->getValue('urapidflow/advanced/disable_changes')) {
            return $this;
        }

        $this->setMassactionIdField('profile_id');
        $this->getMassactionBlock()->setFormFieldName('profiles');

        $this->getMassactionBlock()->addItem('delete', [
            'label' => __('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => __('Are you sure?')
        ]);

        $this->getMassactionBlock()->addItem('profile_status', [
            'label' => __('Change status'),
            'url' => $this->getUrl('*/*/massStatus', ['_current' => true]),
            'additional' => [
                'status' => [
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => __('Status'),
                    'values' => $this->_rapidFlowSource->setPath('profile_status')->toOptionHash()
                ]
            ]
        ]);
        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }

}
