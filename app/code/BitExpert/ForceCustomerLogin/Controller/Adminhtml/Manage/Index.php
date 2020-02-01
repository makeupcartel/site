<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Controller\Adminhtml\Manage;

/**
 * Class Index
 *
 * @package BitExpert\ForceCustomerLogin\Controller\Adminhtml\Manage
 * @codingStandardsIgnoreFile
 */
class Index extends \Magento\Backend\App\Action
{
    /**
     * Index Action*
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('BitExpert_ForceCustomerLogin::bitexpert_force_customer_login_manage');
    }
}