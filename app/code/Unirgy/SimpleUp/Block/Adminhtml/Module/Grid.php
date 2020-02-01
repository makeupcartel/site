<?php
/**
 * \Unirgy\SimpleUp extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Unirgy
 * @package    \Unirgy\SimpleUp
 * @copyright  Copyright (c) 2011 Unirgy LLC
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Unirgy
 * @package    \Unirgy\SimpleUp
 * @author     Boris (Moshe) Gurvich <support@unirgy.com>
 */
namespace Unirgy\SimpleUp\Block\Adminhtml\Module;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Helper\Data as HelperData;
use Unirgy\SimpleUp\Helper\Data as SimpleUpHelperData;
use Unirgy\SimpleUp\Model\ModuleFactory;


class Grid extends Extended implements TabInterface
{

    /**
     * @var SimpleUpHelperData
     */
    protected $_simpleUpHelper;

    /**
     * @var Module
     */
    protected $_moduleFactory;


    public function __construct(Context $context,
                                HelperData $backendHelper,
                                SimpleUpHelperData $helper,
                                ModuleFactory $modelFactory,
                                array $data = [])
    {
        $this->_simpleUpHelper = $helper;
        $this->_moduleFactory = $modelFactory;

        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('modulesGrid');
        $this->setDefaultSort('module_name');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('module_filter');
    }


    protected function _prepareLayout()
    {
        $this->setChild('check_updates_button',
            $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
                ->setData([
                    'label' => __('Check For Updates'),
                    'onclick' => "location.href = '{$this->getUrl('usimpleup/module/checkUpdates')}'",
                    'class' => 'save',
                ])
        );
        return parent::_prepareLayout();
    }

    public function getMainButtonsHtml()
    {
        return parent::getMainButtonsHtml() . $this->getChildHtml('check_updates_button');
    }

    protected function _prepareCollection()
    {
        //$this->_simpleUpHelperData->refreshMeta();
        $this->setCollection($this->_moduleFactory->create()->getCollection());
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('module_name', [
            'header' => __('Module'),
            'index' => 'module_name',
        ]);

        $this->addColumn('download_uri', [
            'header' => __('Download URL'),
            'index' => 'download_uri',
            'renderer' => 'Unirgy\SimpleUp\Block\Adminhtml\Module\RemoteUri',
        ]);

        $this->addColumn('last_downloaded', [
            'header' => __('Last Downloaded'),
            'index' => 'last_downloaded',
            'type' => 'datetime',
            'width' => '160px',
        ]);

        $this->addColumn('current_version', [
            'header' => __('Installed'),
            'index' => 'current_version',
            'width' => '50px',
            'renderer' => 'Unirgy\SimpleUp\Block\Adminhtml\Module\Version',
        ]);

        $this->addColumn('last_checked', [
            'header' => __('Last Checked'),
            'index' => 'last_checked',
            'type' => 'datetime',
            'width' => '160px',
        ]);

        /*
                $this->addColumn('last_stability', [
                    'header'    => __('Last Stability'),
                    'index'     => 'module_name',
                    'width'     => '80px',
                ]);
        */

        $this->addColumn('remote_version', [
            'header' => __('Available'),
            'index' => 'remote_version',
            'width' => '50px',
        ]);

        $this->addColumn('module_actions', [
            'header' => __('Action'),
            'width' => 70,
            'sortable' => false,
            'filter' => false,
            'renderer' => 'Unirgy\SimpleUp\Block\Adminhtml\Module\Action',
        ]);

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('module_id');
        $this->getMassactionBlock()->setFormFieldName('modules');
        $this->getMassactionBlock()->addItem('upgrade', [
            'label' => __('Upgrade / Reinstall'),
            'url' => $this->getUrl('*/*/massUpgrade', ['_current' => true]),
        ]);

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    /**
     * Return Tab label
     *
     * @return string
     * @api
     */
    public function getTabLabel()
    {
        return '';
    }

    /**
     * Return Tab title
     *
     * @return string
     * @api
     */
    public function getTabTitle()
    {
        return '';
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     * @api
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     * @api
     */
    public function isHidden()
    {
        return false;
    }
}
