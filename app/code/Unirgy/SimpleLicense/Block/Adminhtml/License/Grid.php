<?php
/**
 * \Unirgy\StoreLocator extension
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
namespace Unirgy\SimpleLicense\Block\Adminhtml\License;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid as WidgetGrid;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data as HelperData;
use Unirgy\SimpleLicense\Model\License;
use Unirgy\SimpleUp\Helper\Data as SimpleUpHelper;


class Grid extends Extended
{

    /**
     * @var SimpleUpHelper
     */
    protected $_simpleUpHelper;

    /**
     * @var License
     */
    protected $licenseModel;


    public function __construct(Context $context,
                                HelperData $backendHelper,
                                SimpleUpHelper $simpleUpHelper,
                                License $licenseModel,
                                array $data = [])
    {
        $this->_simpleUpHelper = $simpleUpHelper;
        $this->licenseModel = $licenseModel;

        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();

        $this->setId('licensesGrid');
        $this->setDefaultSort('license_key');
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
                    'label' => __('Refresh Licenses'),
                    'onclick' => "location.href = '{$this->getUrl('usimplelic/license/checkUpdates')}'",
                    'class' => 'save',
                ])
        );
        $this->setChild('send_server_info_button',
            $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
                ->setData([
                    'label' => __('Send Server Info'),
                    'onclick' => "location.href = '{$this->getUrl('usimplelic/license/serverInfo')}'",
                    'class' => 'save',
                ])
        );
        return parent::_prepareLayout();
    }

    public function getMainButtonsHtml()
    {
        return parent::getMainButtonsHtml() . $this->getChildHtml('send_server_info_button') . $this->getChildHtml('check_updates_button');
    }

    protected function _prepareCollection()
    {
        //$this->_simpleUpHelperData->refreshMeta();
        $this->setCollection($this->licenseModel->getCollection());
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('license_status', [
            'header' => __('Status'),
            'index' => 'license_status',
            'renderer' => 'Unirgy\SimpleLicense\Block\Adminhtml\License\Status',
        ]);

        $this->addColumn('license_key', [
            'header' => __('License Key'),
            'index' => 'license_key',
            'column_css_class' => 'price', //nowrap
        ]);

        $this->addColumn('products', [
            'header' => __('Products Covered'),
            'index' => 'products',
            'renderer' => 'Unirgy\SimpleUp\Block\Adminhtml\Module\Nl2br',
        ]);

        $this->addColumn('server_restriction', [
            'header' => __('Dev Servers'),
            'index' => 'server_restriction',
            'renderer' => 'Unirgy\SimpleUp\Block\Adminhtml\Module\Nl2br',
        ]);
        $this->addColumn('server_restriction1', [
            'header' => __('Live Servers 1'),
            'index' => 'server_restriction1',
            'renderer' => 'Unirgy\SimpleUp\Block\Adminhtml\Module\Nl2br',
        ]);
        $this->addColumn('server_restriction2', [
            'header' => __('Live Servers 2'),
            'index' => 'server_restriction2',
            'renderer' => 'Unirgy\SimpleUp\Block\Adminhtml\Module\Nl2br',
        ]);

        $this->addColumn('license_expire', [
            'header' => __('License Expires'),
            'index' => 'license_expire',
            'type' => 'date',
            'width' => '160px',
        ]);

        $this->addColumn('upgrade_expire', [
            'header' => __('Upgrades Expire'),
            'index' => 'upgrade_expire',
            'type' => 'date',
            'width' => '160px',
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
        $this->setMassactionIdField('license_id');
        $this->getMassactionBlock()->setFormFieldName('licenses');
        $this->getMassactionBlock()->addItem('remove', [
            'label' => __('Remove'),
            'url' => $this->getUrl('usimplelic/license/massRemove'),
            'confirm' => __('Removing selected licenses(s). Are you sure?')
        ]);

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('usimplelic/license//grid', ['_current' => true]);
    }
}
