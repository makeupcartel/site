<?php

namespace Unirgy\RapidFlow\Controller\Adminhtml\Profile;


class Index extends AbstractProfile
{
    public function execute()
    {
        $this->_checkIssues();

        return $this->_initAction();
    }
}
