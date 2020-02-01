<?php

namespace Unirgy\SimpleUp\Block\Adminhtml\Module;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;


class RemoteUri extends AbstractRenderer
{
    public function render(DataObject $row)
    {
        $uri = isset($row['download_uri']) ? (string)$row['download_uri'] : null;
        $uri .= (strpos($uri, '?') === false ? '?' : '&') . 'php=' . PHP_VERSION;
        if (function_exists('ioncube_loader_version')) {
            $uri .= '&ioncube=' . ioncube_loader_version();
        }
        if (function_exists('sg_get_const')) {
            $uri .= '&sg=1';
        }
        return $uri ? '<a href="'.$uri.'" title="'.$uri.'">Hover/Click</a>' : '';
    }
}
