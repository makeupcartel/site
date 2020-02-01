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
namespace Bss\CustomerApproval\Observer;

use Magento\Framework\Event\ObserverInterface;
use Bss\CustomerApproval\Helper\Data;
use Bss\CustomerApproval\Helper\Email;

class SaveObserver implements ObserverInterface
{
    /**
     * @var Bss\CustomerApproval\Helper\Data
     */
    protected $helper;

    /**
     * @var Bss\CustomerApproval\Helper\Email
     */
    protected $emailHelper;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * SaveObserver constructor.
     * @param Data $helper
     * @param Email $emailHelper
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     */
    public function __construct(
        Data $helper,
        Email $emailHelper,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        $this->helper = $helper;
        $this->emailHelper = $emailHelper;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getCustomer();
        if ($customer->getId()) {
            $customerId = $customer->getId();
            $storeId = $customer->getData('store_id');
            if ($this->helper->isEnableCustomerEmail($storeId)) {
                $status = (int) $customer->getData('activasion_status');

                $pending = $this->helper->getPendingValue();
                $approve = $this->helper->getApproveValue();
                $disapprove = $this->helper->getDisApproveValue();

                $currentCustomer = $this->customerRepositoryInterface->getById($customerId);
                $currentStatusOption = (int) $currentCustomer->getCustomAttribute('activasion_status')->getValue();
                
                $customerEmail = $customer->getEmail();
                $customerName = $customer->getName();
                
                if ($status == $approve) {
                    if ($currentStatusOption == $pending || $currentStatusOption == $disapprove) {
                        /* $emailTemplate = $this->helper->getCustomerApproveEmailTemplate($storeId);
                        $this->emailHelper->sendEmail($customerEmail, $emailTemplate, $customerName, $storeId); */
                    }
                }/* elseif ($status == $disapprove) {
                    if ($currentStatusOption == $pending || $currentStatusOption == $approve) {
                        $emailTemplate = $this->helper->getCustomerDisapproveEmailTemplate($storeId);
                        $this->emailHelper->sendEmail($customerEmail, $emailTemplate, $customerName, $storeId);
                    }
                }*/
            }
        }
    }
}
