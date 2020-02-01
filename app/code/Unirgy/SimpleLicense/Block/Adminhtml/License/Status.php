<?php

namespace Unirgy\SimpleLicense\Block\Adminhtml\License;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Unirgy\SimpleLicense\Model\License;


class Status extends AbstractRenderer
{
    /**
     * @param DataObject|License $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $status = $row->getLicenseStatus();

        switch ($status) {
            case 'inactive':
                $class = 'critical';
                break;
            case 'invalid':
                $class = 'major';
                break;
            case 'expired':
                $class = 'minor';
                break;
            case 'active':
                $class = 'notice';
                break;
            default:
                $class = 'minor';
        }
        return '<span class="grid-severity-' . $class . '"><span>' . $status . '</span></span>';
    }
}
