<?php

namespace Unirgy\SimpleUp\Block\Adminhtml\Module;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Framework\Module\ModuleListInterface;


class Version extends AbstractRenderer
{
    protected $_moduleList;

    public function __construct(Context $context, ModuleListInterface $moduleList, array $data = [])
    {
        $this->_moduleList = $moduleList;
        parent::__construct($context, $data);
    }

    public function render(DataObject $row)
    {
        $moduleName = $row->getData('module_name');
        $curVer = null;
        if ($this->_moduleList->has($moduleName)) {
            $moduleData = $this->_moduleList->getOne($moduleName);
            if($moduleData){
                $curVer = $moduleData['setup_version'];
            }
        }
//        $curVer = (string)$this->_scopeConfig->getValue("modules/{$moduleName}/version");
        $lastVer = $row->getData('remote_version');

        $compare = version_compare($curVer, $lastVer);
        $status = '';
        if (!$lastVer) {
            $status = 'major';
            #return '<span class="grid-severity-minor">'.$curVer.'</span>';
        } elseif ($compare == 0) {
            $status = 'notice';
        } elseif ($compare == -1) {
            $status = 'critical';
            #return '<span class="grid-severity-major">'.$curVer.'</span>';
        } elseif ($compare == 1) {
            $status = 'minor';
            #return '<span class="grid-severity-minor">'.$curVer.'</span>';
        }
        return '<span class="grid-severity-' . $status . '"><span>' . $curVer . '</span></span>';
    }
}
