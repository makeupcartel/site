<?php

namespace Unirgy\RapidFlow\Controller\Adminhtml\Profile;


use Unirgy\RapidFlow\Model\Profile;

class NewAction extends AbstractProfile
{
    public function execute()
    {
        $this->_forward('edit');
    }
}
