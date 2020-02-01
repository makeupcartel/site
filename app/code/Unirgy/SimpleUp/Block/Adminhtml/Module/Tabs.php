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
 * @package    \Unirgy\Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\SimpleUp\Block\Adminhtml\Module;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;


class Tabs extends WidgetTabs
{
    public function _construct()
    {
        parent::_construct();
        $this->setId('usimpleup_module_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle('');
    }

    protected function _beforeToHtml()
    {
        $id = $this->_request->getParam('id', 0);

        $this->_eventManager->dispatch('usimpleup_license_tabs', ['container' => $this]);

        $this->addTab('manage_modules_section', [
            'label' => __('Manage Modules'),
            'title' => __('Manage Modules'),
            'content' => $this->getLayout()
                ->createBlock('Unirgy\SimpleUp\Block\Adminhtml\Module\Grid')->toHtml(),
        ]);

        return parent::_beforeToHtml();
    }
}
