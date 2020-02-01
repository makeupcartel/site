<?php

namespace Unirgy\RapidFlow\Block\Adminhtml\Profile\Grid;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Unirgy\RapidFlow\Model\Source;

class Status extends AbstractRenderer
{
    /**
     * @var Source
     */
    protected $_rapidFlowSource;

    public function __construct(Context $context,
                                Source $rapidFlowSource,
                                array $data = [])
    {
        $this->_rapidFlowSource = $rapidFlowSource;

        parent::__construct($context, $data);
    }

    /**
     * Renders grid column
     *
     * @param   DataObject $row
     * @return  string
     */
    public function render(DataObject $row)
    {
        $key = $this->getColumn()->getIndex();
        $value = $row->getData($key);

        switch ($key) {
            case 'profile_status':
                $classes = ['disabled' => 'critical', 'enabled' => 'notice'];
                $labels = $this->_rapidFlowSource->setPath('profile_status')->toOptionHash();
                break;

            case 'run_status':
                $classes = ['idle' => 'notice', 'pending' => 'minor', 'running' => 'major', 'paused' => 'minor', 'stopped' => 'critical', 'finished' => 'notice'];
                $labels = $this->_rapidFlowSource->setPath('run_status')->toOptionHash();
                break;

            case 'invoke_status':
                $classes = ['none' => 'minor', 'foreground' => 'critical', 'ondemand' => 'notice', 'scheduled' => 'major'];
                $labels1 = ['foreground' => __('ForeGrnd'), 'ondemand' => __('OnDemand'), 'scheduled' => __('Schedule')];
                $labels = $this->_rapidFlowSource->setPath('invoke_status')->toOptionHash();
                break;

            default:
                return $value;
        }

        return '<span class="grid-severity-' . $classes[$value] . '" ' . (!empty($styles[$value]) ? ' style="' . $styles[$value] . '"' : '') . '><span>'
        . (!empty($labels1[$value]) ? $labels1[$value] : $labels[$value])
        . '</span></span>';
    }
}
