<?php

namespace Convert\CustomerContent\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;

class NewAction extends Action
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
