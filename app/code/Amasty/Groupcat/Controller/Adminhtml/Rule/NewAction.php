<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

namespace Amasty\Groupcat\Controller\Adminhtml\Rule;

class NewAction extends \Amasty\Groupcat\Controller\Adminhtml\Rule
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
