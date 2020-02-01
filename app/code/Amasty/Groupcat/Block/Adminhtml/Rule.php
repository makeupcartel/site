<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

namespace Amasty\Groupcat\Block\Adminhtml;

class Rule extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller     = 'rule';
        $this->_headerText     = __('Customer Group Catalog Rules');
        $this->_addButtonLabel = __('Add New Rule');
        parent::_construct();
    }
}
