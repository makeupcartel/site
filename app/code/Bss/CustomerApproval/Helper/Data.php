<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomerApproval
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerApproval\Helper;

use Bss\CustomerApproval\Model\ResourceModel\Options;
use Magento\Framework\App\Helper\Context;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Options $optionModel
     */
    protected $optionModel;

    /**
     * Data constructor.
     * @param Context $context
     * @param Options $optionModel
     */
    public function __construct(
        Context $context,
        Options $optionModel
    ) {
        parent::__construct($context);
        $this->optionModel = $optionModel;
    }

    /**
     * Get Enable|Disable
     * @return bool
     */
    public function isEnable()
    {
        return $this->scopeConfig->isSetFlag(
            'customer_approval/general/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * @return true|false
     */
    public function isEnableAdminEmail()
    {
        return $this->scopeConfig->isSetFlag(
            'customer_approval/admin_notification/admin_notification_enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return true|false
     */
    public function isEnableCustomerEmail($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            'customer_approval/email_setting/customer_email_enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return true|false
     */
    public function isAutoApproval()
    {
        return $this->scopeConfig->isSetFlag(
            'customer_approval/general/auto_approval',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getPendingMess()
    {
        $pendingMess= $this->scopeConfig->getValue(
            'customer_approval/general/pending_message',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $pendingMess;
    }

    /**
     * @return string
     */
    public function getAdminEmailTemplate()
    {
        $emailTemplate= $this->scopeConfig->getValue(
            'customer_approval/admin_notification/admin_email_templates',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $emailTemplate;
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getCustomerApproveEmailTemplate($storeId = null)
    {
        $customerApproveEmailTemplate= $this->scopeConfig->getValue(
            'customer_approval/email_setting/customer_approve_templates',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $customerApproveEmailTemplate;
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getCustomerDisapproveEmailTemplate($storeId = null)
    {
        $customerDisapproveEmailTemplate= $this->scopeConfig->getValue(
            'customer_approval/email_setting/customer_disapprove_templates',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $customerDisapproveEmailTemplate;
    }

    /**
     * @return string
     */
    public function getAdminEmailSender()
    {
        $emailSender= $this->scopeConfig->getValue(
            'customer_approval/admin_notification/admin_email_sender',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $emailSender;
    }

    /**
     * @return string
     */
    public function getAdminEmail()
    {
        $emailAdmin= $this->scopeConfig->getValue(
            'customer_approval/admin_notification/admin_recipeints',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $emailAdmin;
    }

    /**
     * @return string
     */
    public function getCustomerEmailSender()
    {
        $customerEmailSender= $this->scopeConfig->getValue(
            'customer_approval/email_setting/customer_email_sender',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $customerEmailSender;
    }

    /**
     * @return string
     */
    public function getDisapproveMess()
    {
        $pendingMess= $this->scopeConfig->getValue(
            'customer_approval/general/disapprove_message',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $pendingMess;
    }

    /**
     * @return int
     */
    public function getPendingValue()
    {
        $pending = (int) $this->optionModel->getStatusValue('Pending')['option_id'];
        return $pending;
    }

    /**
     * @return int
     */
    public function getApproveValue()
    {
        $approve = (int) $this->optionModel->getStatusValue('Approved')['option_id'];
        return $approve;
    }

    /**
     * @return int
     */
    public function getDisApproveValue()
    {
        $disapprove = (int) $this->optionModel->getStatusValue('Disapproved')['option_id'];
        return $disapprove;
    }
}
