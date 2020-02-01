<?php

namespace Unirgy\SimpleUp\Block\Adminhtml\Module;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;


class Action extends AbstractRenderer
{
    public function render(DataObject $row)
    {
        $usimpleup = $this->_scopeConfig->getValue("modules/{$row->getData('module_name')}/usimpleup");
        return isset($usimpleup['changelog']) ? '<a href="'.$usimpleup['changelog'].'">'.__('Changelog').'</a>' : '';
    }
}
