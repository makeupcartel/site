<?php

namespace Unirgy\SimpleUp\Block\Adminhtml\Module;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;


class Nl2br extends AbstractRenderer
{
    public function render(DataObject $row)
    {
        return nl2br($this->_getValue($row));
    }
}
