<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Block\Adminhtml;

class Request extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller     = 'request';
        $this->_headerText     = __('Get a Quote Requests');
        parent::_construct();
    }

    protected function _addNewButton()
    {
        return;//remove new button
    }
}
