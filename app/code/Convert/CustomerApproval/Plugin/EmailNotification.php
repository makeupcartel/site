<?php

namespace Convert\CustomerApproval\Plugin;

use Magento\Customer\Api\Data\CustomerInterface;
use Convert\CustomerApproval\Helper\Data;
use Bss\CustomerApproval\Helper\Email;
use Magento\Customer\Model\EmailNotification as EmailNotificationDefault;
use Magento\Customer\Model\EmailNotificationInterface;

/**
 * Class EmailNotification
 *
 * @package Convert\CustomerApproval\Plugin
 */
class EmailNotification
{
    const XML_PATH_REGISTER_EMAIL_TEMPLATE = 'customer/create_account/email_template';
    const XML_PATH_REGISTER_NO_PASSWORD_EMAIL_TEMPLATE = 'customer/create_account/email_no_password_template';
    const XML_PATH_CONFIRM_EMAIL_TEMPLATE = 'customer/create_account/email_confirmation_template';
    const XML_PATH_CONFIRMED_EMAIL_TEMPLATE = 'customer/create_account/email_confirmed_template';

    const TEMPLATE_TYPES = [
        EmailNotificationDefault::NEW_ACCOUNT_EMAIL_REGISTERED => self::XML_PATH_REGISTER_EMAIL_TEMPLATE,
        EmailNotificationDefault::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD => self::XML_PATH_REGISTER_NO_PASSWORD_EMAIL_TEMPLATE,
        EmailNotificationDefault::NEW_ACCOUNT_EMAIL_CONFIRMED => self::XML_PATH_CONFIRMED_EMAIL_TEMPLATE,
        EmailNotificationDefault::NEW_ACCOUNT_EMAIL_CONFIRMATION => self::XML_PATH_CONFIRM_EMAIL_TEMPLATE,
    ];

    /**
     * @var Data 
     */
    protected $helper;

    /**
     * @var Email 
     */
    protected $emailHelper;

    /**
     * EmailNotification constructor.
     *
     * @param Data $helper
     * @param Email $emailHelper
     */
    public function __construct(
        Data $helper,
        Email $emailHelper
    ) {
        $this->helper = $helper;
        $this->emailHelper = $emailHelper;
    }

    /**
     * @param EmailNotificationDefault $subject
     * @param callable $proceed
     * @param CustomerInterface $customer
     * @param string $type
     * @param string $backUrl
     * @param int $storeId
     * @param null $sendEmailStoreId
     * @return bool
     */
    public function aroundNewAccount(
        \Magento\Customer\Model\EmailNotification $subject,
        callable $proceed,
        \Magento\Customer\Api\Data\CustomerInterface $customer,
        $type = \Magento\Customer\Model\EmailNotificationInterface::NEW_ACCOUNT_EMAIL_REGISTERED,
        $backUrl = '',
        $storeId = 0,
        $sendEmailStoreId = null
    ) {
        $enable = $this->helper->isEnable();
        $isAutoApproval = $this->helper->isAutoApproval();
        if ($type == EmailNotificationDefault::NEW_ACCOUNT_EMAIL_REGISTERED && $enable && !$isAutoApproval) {
            if ($this->helper->isForceApproveSendEnable()) {
                $emailTemplate = $this->helper->getCustomerApproveEmailTemplate($customer->getStoreId());
                $this->emailHelper->sendEmail($customer->getEmail(), $emailTemplate, $customer->getFirstName(), $storeId);
            }
            return false;
        } else {
            return $proceed($customer, $type, $backUrl, $storeId, $sendEmailStoreId);
        }
    }

}
