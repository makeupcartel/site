<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

namespace Amasty\Groupcat\Controller\Adminhtml\Rule;

class Index extends \Amasty\Groupcat\Controller\Adminhtml\Rule
{
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Amasty Customer Group Catalog Rules'));
        $this->_view->renderLayout();
    }
}
