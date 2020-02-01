<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Controller\Adminhtml\Request;

class Index extends \Amasty\Groupcat\Controller\Adminhtml\Request
{
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Get a Quote Requests'));
        $this->_view->renderLayout();
    }
}
